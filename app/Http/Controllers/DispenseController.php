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
     * 2. The API Endpoint the ESP32 calls every few seconds
     */
    public function checkPending()
    {
        $pending = Dispensal::where('status', 'pending')
                            ->with('medication')
                            ->orderBy('created_at', 'asc')
                            ->first();

        if ($pending) {
            return response()->json([
                'command' => 'dispense',
                'dispensal_id' => $pending->id,
                'slot' => $pending->medication->hardware_slot_id,
                'med_id' => $pending->medication->id
            ]);
        }

        return response()->json(['command' => 'wait']);
    }

    /**
     * 3. The API Endpoint the ESP32 calls AFTER moving the servo
     */
    public function confirmDispense($id)
    {
        $log = Dispensal::find($id);

        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        if ($log->status === 'success') {
            return response()->json(['status' => 'already confirmed']);
        }

        $log->update(['status' => 'success']);

        $med = Medication::find($log->medication_id);
        if ($med) {
            $med->decrement('stock_level');
        }

        return response()->json(['status' => 'confirmed']);
    }
}
