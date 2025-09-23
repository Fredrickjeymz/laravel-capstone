<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Helpers\ActivityLogger;
use App\Notifications\StudentAddedToClass;
use Illuminate\Support\Facades\Log;

class ClassAssignmentController extends Controller
{
    public function index(Request $request)
    {
        // make sure user is present
        $teacher = auth()->guard('web')->user();
        if (!$teacher) {
            if ($request->ajax()) {
                return response('Unauthenticated', 401);
            }
            return redirect()->route('login');
        }

        try {
            $classes = $teacher->classes()->get();
            $classIds = $classes->pluck('id')->toArray();

            // if teacher has no classes, return empty set quickly
            if (empty($classIds)) {
                $students = collect([]);
                $allStudents = Student::all();
                return view('Students', compact('students', 'classes', 'allStudents'));
            }

            $studentsQuery = Student::whereHas('classes', function ($q) use ($classIds) {
                $q->whereIn('school_class_id', $classIds);
            })->with(['classes' => function ($q) use ($classIds) {
                $q->whereIn('school_class_id', $classIds);
            }]);

            if ($request->filled('search')) {
                $search = $request->input('search');
                $studentsQuery->where(function ($q) use ($search) {
                    $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('mname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('gender', 'like', "%{$search}%")
                    // safe date search: format date to string
                    ->orWhereRaw("DATE_FORMAT(birthdate, '%Y-%m-%d') LIKE ?", ["%{$search}%"]);
                });
            }

            $students = $studentsQuery->get();
            $allStudents = Student::all();

            return view('Students', compact('students', 'classes', 'allStudents'));
        } catch (\Throwable $e) {
            Log::error('Students#index error: '.$e->getMessage()."\n".$e->getTraceAsString());
            if ($request->ajax()) {
                return response('Server error: '.$e->getMessage(), 500);
            }
            abort(500);
        }
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

        $student = \App\Models\Student::with('classes')->find($validated['student_id']);

        $teacher = auth('web')->user();
        $student->notify(new StudentAddedToClass($class, $teacher));
        
            ActivityLogger::log(
                "Added Student to Class",
                "Student: {$student->fname} {$student->lname} was added to {$class->class_name} ({$class->year_level}), Subject: {$class->subject}, by Teacher: {$teacher->fname} {$teacher->lname}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Student successfully assigned to the class.',
            'student' => $student,   // 👈 return student object
            'class'   => $class      // 👈 return class object
        ]);
    }

}
