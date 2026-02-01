<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\Medication;
use App\Models\Dispensal;

class DispenseController extends Controller
{
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

        // Hardware Trigger - Replace with IP of the ESP32
        $esp32_ip = 'http://192.168.1.50/api/dispense';

        try {
            $response = Http::timeout(5)->post($esp32_ip, [
                'slot' => $med->hardware_slot_id,
                'med_id' => $med->id
            ]);

            if ($response->successful()) {
                $log->update(['status' => 'success']);
                $med->decrement('stock_level');
                return response()->json(['status' => 'success']);
            } else {
                $log->update(['status' => 'failed']);
                return response()->json(['status' => 'error'], 500);
            }
        } catch (\Exception $e) {
            $log->update(['status' => 'failed']);
            return response()->json(['status' => 'error', 'message' => 'Hardware Offline'], 500);
        }
    }
}
