<?php

namespace App\Http\Controllers;
use App\Models\Assessment;
use App\Models\SavedAssessment;
use Illuminate\Http\Request;
use App\Models\AssessmentQuestion;
use App\Models\Teacher;
use App\Models\QuestionType;
use Carbon\Carbon;

class CountsAdmin extends Controller
{
   
    public function dashboard()
    {
        $assessmentCount = Assessment::count();
        $questionCount = AssessmentQuestion::count();
        $teacherCount = Teacher::count();
        $questionTypeCount = QuestionType::count();
        $generatedAssessments = Assessment::with('teacher')->latest()->take(4)->get();

        $assessments = Assessment::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Prepare labels and data
        $monthLabels = [];
        $assessmentCounts = [];

        foreach ($assessments as $item) {
            $monthName = Carbon::create()->month($item->month)->format('F');
            $monthLabels[] = $monthName;
            $assessmentCounts[] = $item->count;
        }

        return view('AdminDashboard', compact(
            'assessmentCount',
            'questionCount',
            'teacherCount',
            'questionTypeCount',
            'monthLabels', 
            'assessmentCounts',
            'generatedAssessments'
        ));
        
    }
}
