<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\SchoolClass;

class StudentClassController extends Controller
{
    public function index()
    {
        $student = auth()->guard('student')->user();
        $studentId = $student->id;

        // Load teacher and assessments with due dates
        $classes = $student->classes()
            ->with(['teacher', 'assessments' => function ($query) {
                $query->withPivot('due_date');
            }])
            ->get();

        // Map additional quiz info to each class
        foreach ($classes as $class) {
            $totalQuizzes = $class->assessments->count();

            // Quizzes this student already took
            $taken = \App\Models\StudentAssessmentScore::where('student_id', $studentId)
                ->whereIn('assessment_id', $class->assessments->pluck('id'))
                ->pluck('assessment_id')
                ->toArray();

            // Count pending quizzes (assigned but not yet taken)
            $pending = $class->assessments->whereNotIn('id', $taken)->count();

            $class->total_quizzes = $totalQuizzes;
            $class->pending_quizzes = $pending;
        }

        return view('StudentClasses', compact('classes'));
    }

}
