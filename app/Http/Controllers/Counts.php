<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Assessment;
use App\Models\AssessmentClass;
use App\Models\SavedAssessment;
use App\Models\AssessmentQuestion;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Counts extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $userId = $user->id;

        // 1. Generated Assessments (this month)
        $generatedAssessmentsCount = Assessment::where('teacher_id', $userId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // 2. Saved Assessments
        $savedAssessmentsCount = AssessmentClass::whereHas('assessment', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })->count();


        // 3. Questions Created
        $questionsCount = AssessmentQuestion::whereHas('assessment', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })->count();

        // 4. Average of Question
        // 4. Average number of questions per assessment
        $teacherAssessments = Assessment::where('teacher_id', $userId)->withCount('questions')->get();
        $totalAssessments = $teacherAssessments->count();
        $totalQuestions = $teacherAssessments->sum('questions_count');

        $optionsCount = $totalAssessments > 0 ? round($totalQuestions / $totalAssessments, 2) : 0;

        $generatedAssessments = Assessment::where('teacher_id', Auth::id())->latest()->take(4)->get();

        // Get class IDs the teacher owns
        $teacherClassIds = SchoolClass::where('teacher_id', $userId)->pluck('id');

        $classesCount = $teacherClassIds->count();

        // Count distinct students from the pivot table
        $studentsCount = DB::table('class_student')
            ->whereIn('school_class_id', $teacherClassIds)
            ->distinct('student_id')
            ->count('student_id');

        return view('TeacherDashboard', compact(
            'generatedAssessmentsCount',
            'savedAssessmentsCount',
            'generatedAssessments',
            'studentsCount',
            'classesCount'
        ));
    }

}
