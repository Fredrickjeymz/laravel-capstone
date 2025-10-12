<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Assessment;
use App\Models\AssessmentClass;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class AssignedAssController extends Controller
{
    public function AssignedAss(Request $request)
    {
        $teacher = auth()->guard('web')->user();

        // Get assessments assigned by this teacher
        $assignedAss = AssessmentClass::with([
            'assessment' => function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                    ->withCount('questions')
                    ->with('teacher');
            },
            'class.students', // For total students in class
            'assessment.studentScores', // For answered count
        ])->get();

        $now = now(); // For status logic

        return view('AssignedAssessments', compact('assignedAss', 'now'));
    }

    public function viewStudents($classId)
    {
        $class = SchoolClass::with('students')->findOrFail($classId);
        
        // Get all students NOT in this class for the datalist
        $allStudents = \App\Models\Student::whereDoesntHave('classes', function($query) use ($classId) {
            $query->where('school_class_id', $classId);
        })->get();

        return view('students-in-class', [
            'class' => $class,
            'students' => $class->students,
            'allStudents' => $allStudents // Students available to add
        ]);
    }

    public function addToClass(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_class_id' => 'required|exists:school_classes,id',
        ]);

        $class = \App\Models\SchoolClass::find($validated['school_class_id']);
        $student = \App\Models\Student::find($validated['student_id']);

        // Check if already assigned
        if ($class->students()->where('student_id', $validated['student_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Student is already assigned to this class.'
            ], 409);
        }

        $class->students()->attach($validated['student_id']);
        $student->load('classes');

        // Log activity and send notification
        $teacher = auth('web')->user();
        ActivityLogger::log(
            "Added Student to Class",
            "Student: {$student->fname} {$student->lname} was added to {$class->class_name} by Teacher: {$teacher->fname} {$teacher->lname}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Student successfully assigned to the class.',
            'student' => $student
        ]);
    }

    public function removeStudent(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:school_classes,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $class = SchoolClass::findOrFail($request->input('class_id'));

        // ✅ Use the teacher guard explicitly
        $teacherId = auth('web')->id(); // or: Auth::guard('teacher')->id()

        if (!$teacherId) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // ✅ Compare IDs as ints
        if ((int) $class->teacher_id !== (int) $teacherId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ✅ Detach the student from this class (pivot: class_student)
        $class->students()->detach($request->input('student_id'));

        // 📝 Log
        $student = \App\Models\Student::find($request->input('student_id'));
        ActivityLogger::log(
            "Removed Student from Class",
            "Student: {$student->fname} {$student->lname}, Class: {$class->class_name} ({$class->year_level})"
        );


        return response()->json(['success' => true]);
    }
}
