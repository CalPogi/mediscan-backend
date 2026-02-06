<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\GradeLevel;

class StudentController extends Controller
{

    public function getSchoolData()
    {
        $data = GradeLevel::with('sections')->get();
        return response()->json($data);
    }

    public function checkLRN($lrn)
    {
        $student = Student::with('section.gradeLevel')->where('lrn', $lrn)->first();

        if ($student) {
            return response()->json([
                'status' => 'found',
                'action' => 'go_to_symptoms',
                'student' => $student
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'action' => 'go_to_register',
            'lrn_input' => $lrn
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'lrn' => 'required|unique:students,lrn',
            'name' => 'required|string',
            'sex'  => 'required|in:Male,Female',
            'section_id' => 'required|exists:sections,id',
            'guardian_name' => 'required|string',
            'guardian_contact' => 'required|string',
        ]);

        $student = Student::create($validated);

        return response()->json(['status' => 'success', 'student' => $student]);
    }
}
