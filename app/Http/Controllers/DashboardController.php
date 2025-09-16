<?php

namespace App\Http\Controllers;
use App\Models\StudentAssessmentScore;
use App\Models\AssessmentClass;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $student = Auth::guard('student')->user(); // ✅ Make sure student guard is used

        // ✅ Completed quizzes by student
        $completedScores = StudentAssessmentScore::where('student_id', $student->id)->get();
        $completedQuizzes = $completedScores->count();

        // ✅ Average score calculation
        $averageScore = $completedScores->count() > 0
            ? round($completedScores->avg('percentage'), 2)
            : 0;

        // ✅ Classes the student is enrolled in
        $classIds = $student->classes->pluck('id'); // from class_student pivot
        $classCount = $classIds->count();

        // ✅ All assessments assigned to these classes
        $assignedAssessments = Assessment::whereHas('assignedClasses', function ($query) use ($classIds) {
            $query->whereIn('school_class_id', $classIds);
        })->get();

        // ✅ Pending quizzes = assigned but not yet completed by the student
        $pendingQuizzes = $assignedAssessments->filter(function ($assessment) use ($student) {
            return !$assessment->studentScores->contains('student_id', $student->id);
        })->count();

        $now = Carbon::now();
        $pendingQuizList = $assignedAssessments->filter(function ($assessment) use ($student, $now) {
            // ✅ Has the student already taken this quiz?
            $hasTaken = $assessment->studentScores->contains('student_id', $student->id);

            // ✅ Get the assigned class (first or specific match)
            $assignedClass = $assessment->assignedClasses->first();

            // ✅ Check due date (if available)
            $dueDate = $assignedClass ? $assignedClass->pivot->due_date : null;

            // 👉 Only keep quizzes that are not overdue
            $isNotOverdue = !$dueDate || Carbon::parse($dueDate)->gte($now);

            return !$hasTaken && $isNotOverdue;
        });

        return view('StudentDashboard', compact(
            'completedQuizzes',
            'averageScore',
            'pendingQuizzes',
            'classCount',
            'pendingQuizList'
        ));
    }
}
