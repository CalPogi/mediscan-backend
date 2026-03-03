<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Medication;
use App\Models\Dispensal;
use Illuminate\Support\Facades\Http;

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
    // ... (Your existing security check)
    $log = Dispensal::find($id);
    if (!$log || $log->status === 'completed') {
        return response()->json(['error' => 'Invalid Log'], 400);
    }

    $log->update(['status' => 'completed']);

    $med = Medication::find($log->medication_id);
    $student = Student::find($log->student_id); // Get student info for the text

    if ($med) {
        $med->decrement('stock_level');

        // --- SMS TRIGGER START ---
        $this->sendSmsToNurse($student, $med);
        // --- SMS TRIGGER END ---
    }

    return response()->json(['status' => 'completed']);
}

/**
 * Helper function to handle the SMS API call
 */
private function sendSmsToNurse($student, $med)
{
    $apikey = env('SEMAPHORE_API_KEY');
    $nurseNumber = env('NURSE_PHONE_NUMBER');

    $message = "ADVISORY: Student {$student->name} (LRN: {$student->lrn}) has dispensed 1 unit of {$med->name} at " . now()->format('h:i A');

    try {
        Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => $apikey,
            'number' => $nurseNumber,
            'message' => $message,
            'sendername' => 'SEMAPHORE'
        ]);
    } catch (\Exception $e) {
        \Log::error("SMS Failed: " . $e->getMessage());
    }
}
}
