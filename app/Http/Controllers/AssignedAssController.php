<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Assessment;
use App\Models\AssessmentClass;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

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

        // Pass both the class and its students to the view
        return view('students-in-class', [
            'class' => $class,
            'students' => $class->students
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

        return response()->json(['success' => true]);
    }
}
