<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;

class AssessmentController extends Controller
{
    public function evaluate(Request $request)
    {
        $symptoms = $request->input('symptoms', []);
        $painLevel = $request->input('pain_level', 0);

        // We accept these inputs so the frontend doesn't break,
        // but we won't use them to block medication anymore.
        $sleep = $request->input('sleep_hours');
        $water = $request->input('water_intake');
        $meal = $request->input('last_meal');

        $recommendation = null;

        if ($painLevel >= 4) {
            return response()->json([
                'recommendation' => 'None',
                'med_id' => null
            ]);
        }

        if (in_array('Headache', $symptoms) || in_array('Fever', $symptoms)) {
            $recommendation = 'Biogesic';
        }
        elseif (in_array('Cough', $symptoms) || in_array('Colds', $symptoms)) {
            $recommendation = 'Neozep';
        }

        if (!$recommendation) {
             return response()->json([
                 'recommendation' => 'None',
                 'med_id' => null
             ]);
        }

        $med = Medication::where('name', $recommendation)->first();

        if (!$med || $med->stock_level <= 0) {
            return response()->json(['error' => "Medication ($recommendation) out of stock."], 400);
        }

        return response()->json([
            'recommendation' => $med->name,
            'med_id' => $med->id,
            'dosage' => $med->dosage,
            'slot' => $med->hardware_slot_id
        ]);
    }
}
