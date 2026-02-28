<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Medication;
use App\Models\Dispensal;
use Carbon\Carbon;

class DispenseController extends Controller
{
    /**
     * Helper to verify the ESP8266 Hardware API Key
     */
    private function isHardwareAuthenticated(Request $request)
    {
        return $request->header('X-API-KEY') === env('ESP_API_KEY');
    }

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
        if (!$this->isHardwareAuthenticated($request)) {
            return response()->json(['error' => 'Unauthorized Hardware'], 401);
        }

        // 🚨 FIX: Removed the Carbon::now() 5-minute window check to bypass timezone issues
        $pending = Dispensal::where('status', 'pending')
                            ->with('medication')
                            ->orderBy('created_at', 'asc')
                            ->first();

        if ($pending) {
            $pending->update(['status' => 'processing']);

            return response()->json([
                'command' => 'DISPENSE',
                'dispensal_id' => $pending->id,
                'slot' => $pending->medication->hardware_slot_id,
                'med_id' => $pending->medication->id
            ]);
        }

        return response()->json(['command' => 'IDLE']);
    }

    /**
     * 3. The API Endpoint the hardware calls AFTER dropping the pill
     */
    public function confirmDispense(Request $request, $id)
    {
        if (!$this->isHardwareAuthenticated($request)) {
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
