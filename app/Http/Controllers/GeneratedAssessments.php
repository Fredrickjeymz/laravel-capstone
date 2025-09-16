<?php

namespace App\Http\Controllers;
use App\Models\ArchivedAssessment;
use App\Models\ArchivedAssessmentQuestion;
use Illuminate\Support\Facades\DB;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;

class GeneratedAssessments extends Controller
{
    public function AssessmentIndex(Request $request)
    {
        $query = Assessment::with('teacher', 'questions'); 

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhere('question_type', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $assessments = $query->latest()->get(); 

        return view('GeneratedAssessments', compact('assessments'));
    }
    
    public function show($id)
    {
        $assessment =Assessment::with('questions')->findOrFail($id);

        return view('AdminAssessmentPreview', compact('assessment'));
    }

    public function Archivedshow(Request $request)
    {
        $query = ArchivedAssessment::with('questions', 'teacher', 'archivedteacher');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhere('question_type', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $assessments = $query->latest()->get(); 
        
        return view('ArchivedAssessments', compact('assessments'));
    }

}
