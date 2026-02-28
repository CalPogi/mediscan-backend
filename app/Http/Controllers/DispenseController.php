<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Medication;
use App\Models\Dispensal;

class DispenseController extends Controller
{
    /**
     * 1. Triggered by your web frontend when a user clicks "Dispense"
     */
    public function dispense(Request $request)
    {
        $student = Student::where('lrn', $request->lrn)->first();
        $med = Medication::find($request->med_id);

        if (!$student || !$med) {
            return response()->json(['error' => 'Invalid Data'], 400);
        }

        $log = Dispensal::create([
            'student_id' => $student->id,
            'medication_id' => $med->id,
            'symptoms' => json_encode($request->symptoms),
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'queued',
            'dispensal_id' => $log->id,
            'message' => 'Waiting for hardware to pick up request'
        ]);
    }

    /**
     * 2. The API Endpoint the ESP32/ESP8266 calls every few seconds
     */
    public function checkPending(Request $request)
    {
        $configuredKey = env('ESP_API_KEY', 'hopiamanipopcorn');

        if ($request->header('X-API-KEY') !== $configuredKey) {
            return response()->json(['error' => 'Unauthorized Hardware'], 401);
        }

        // REMOVED ->with('medication') to fix the 500 Crash
        $pending = Dispensal::where('status', 'pending')
                            ->orderBy('created_at', 'asc')
                            ->first();

        if ($pending) {
            // Find the medication manually to avoid relationship errors
            $medication = Medication::find($pending->medication_id);
            $slot = $medication ? $medication->hardware_slot_id : 0;

            if ($slot > 0) {
                // Good to go! Lock it and send to hardware.
                $pending->update(['status' => 'processing']);
                return response()->json([
                    'command' => 'DISPENSE',
                    'dispensal_id' => $pending->id,
                    'slot' => (int)$slot
                ]);
            } else {
                // 🛡️ JAM PREVENTION: If no valid slot is found, mark as failed so it doesn't block the line forever
                $pending->update(['status' => 'failed']);
            }
        }

        return response()->json(['command' => 'IDLE']);
    }

    /**
     * 3. The API Endpoint the hardware calls AFTER dropping the pill
     */
    public function confirmDispense(Request $request, $id)
    {
        $configuredKey = env('ESP_API_KEY', 'hopiamanipopcorn');

        if ($request->header('X-API-KEY') !== $configuredKey) {
            return response()->json(['error' => 'Unauthorized Hardware'], 401);
        }

        $log = Dispensal::find($id);

        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        if ($log->status === 'completed') {
            return response()->json(['status' => 'already completed']);
        }

        $log->update(['status' => 'completed']);

        $med = Medication::find($log->medication_id);
        if ($med) {
            $med->decrement('stock_level');
        }

        return response()->json(['status' => 'completed']);
    }
}
