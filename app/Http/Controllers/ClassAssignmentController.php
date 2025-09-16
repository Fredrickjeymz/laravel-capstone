<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Teacher;

class ClassAssignmentController extends Controller
{
    public function index()
    {
        $teacher = auth()->guard('web')->user();

        // Get all classes of this teacher
        $classes = $teacher->classes()->get(); // used for the <select> in modal

        // Get class IDs
        $classIds = $classes->pluck('id');

        // Get students enrolled in these classes
        $students = Student::whereHas('classes', function ($query) use ($classIds) {
            $query->whereIn('school_class_id', $classIds);
        })->with(['classes' => function ($q) use ($classIds) {
            $q->whereIn('school_class_id', $classIds);
        }])->get();

        // Also get ALL students for dropdown (optional: filter by school or something)
        $allStudents = Student::all();

        return view('Students', compact('students', 'classes', 'allStudents'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_class_id' => 'required|exists:school_classes,id',
        ]);

        $class = \App\Models\SchoolClass::find($validated['school_class_id']);

        // Check if already assigned
        if ($class->students()->where('student_id', $validated['student_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Student is already assigned to this class.'
            ], 409);
        }

        $class->students()->attach($validated['student_id']);

        // Load student with updated classes
        $student = \App\Models\Student::with('classes')->find($validated['student_id']);

        return response()->json([
            'success' => true,
            'message' => 'Student successfully assigned to the class.',
            'student' => $student,   // 👈 return student object
            'class'   => $class      // 👈 return class object
        ]);
    }

}
