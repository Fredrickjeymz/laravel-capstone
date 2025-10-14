<?php

namespace App\Http\Controllers;

use App\Models\ArchivedAssessment;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\AssessmentClass;
use App\Models\AssessmentType;
use App\Models\QuestionType;
use App\Models\StudentAssessmentScore;
use App\Models\StudentAssessmentQuestionScore;
use App\Models\SavedStudentAssessmentScore;
use App\Models\SavedStudentAssessmentQuestionScore;
use App\Helpers\ActivityLogger;
  use Illuminate\Support\Facades\View;

class AssessmentController extends Controller
{

    public function checkStatus($id)
    {
        $assessment = \App\Models\Assessment::where('id', $id)
            ->where('teacher_id', Auth::id())
            ->select('status')
            ->first();

        if (!$assessment) {
            return response()->json([
                'success' => false,
                'status'  => 'not_found'
            ]);
        }

        return response()->json([
            'success' => true,
            'status'  => $assessment->status
        ]);
    }

    public function saveAssessment($id)
    {
    $assessment = Assessment::with('questions')->findOrFail($id);

    // Step 1: Save the assessment
    $saved =Assessment::create([
        'teacher_id'    => $assessment->teacher_id,
        'title'         => $assessment->title,
        'instructions'  => $assessment->instructions,
        'subject'       => $assessment->subject,
        'question_type' => $assessment->question_type,
        'rubric'        => $assessment->rubric,
    ]);

    // Step 2: Save questions and map original question IDs to saved question IDs
    $questionIdMap = [];

    foreach ($assessment->questions as $question) {
        $savedQuestion = AssessmentQuestion::create([
            'saved_assessment_id' => $saved->id,
            'question_text'       => $question->question_text,
            'options'             => $question->options,
            'answer_key'          => $question->answer_key,
            'sequence_number'     => $question->sequence_number,
        ]);

        // Create the compound key for the mapping
        $normalizedText = strtolower(trim(preg_replace('/\s+/', ' ', $question->question_text)));
        $compoundKey = $normalizedText . '_' . $question->sequence_number;

        // Map the saved question ID
        $questionIdMap[$compoundKey] = $savedQuestion->id;

        Log::info("Saved question (ID: {$savedQuestion->id}) mapped with key: {$compoundKey}");
    }

    // Step 3: Get all student scores related to this assessment
    $studentScores = StudentAssessmentScore::where('assessment_id', $assessment->id)->get();

    foreach ($studentScores as $score) {
        // Step 4: Save overall student score
        $savedScore = SavedStudentAssessmentScore::create([
            'saved_assessment_id' => $saved->id,
            'student_name'        => $score->student_name,
            'student_file_path'   => $score->student_file_path,
            'total_score'         => $score->total_score,
            'max_score'           => $score->max_score,
            'percentage'          => $score->percentage,
            'remarks'             => $score->remarks,
        ]);

        // Step 5: Save question-level scores
        $questionScores = StudentAssessmentQuestionScore::where('student_assessment_score_id', $score->id)->get();

        foreach ($questionScores as $qScore) {
            // Get the original question associated with the score
            $originalQuestion = AssessmentQuestion::find($qScore->assessment_question_id);
            
            if (!$originalQuestion) {
                Log::warning("Original question not found for question score ID: {$qScore->id}");
                continue;
            }

            // Build the compound key to match with the saved questions
            $normalizedText = strtolower(trim(preg_replace('/\s+/', ' ', $originalQuestion->question_text)));
            $compoundKey = $normalizedText . '_' . $originalQuestion->sequence_number;

            Log::info("Looking for key: {$compoundKey}");

            // Check if the key exists in the map
            if (!isset($questionIdMap[$compoundKey])) {
                Log::warning("Key not found in questionIdMap: {$compoundKey}");
                Log::debug("Available keys: " . implode(', ', array_keys($questionIdMap)));
                continue;
            }

            $savedQid = $questionIdMap[$compoundKey];

            // Save the individual question score
            SavedStudentAssessmentQuestionScore::create([
                'saved_student_assessment_score_id' => $savedScore->id,
                'saved_assessment_question_id'      => $savedQid,
                'student_answer'                    => $qScore->student_answer,
                'score_given'                       => $qScore->score_given,
                'max_score'                         => $qScore->max_score,
                'criteria_scores'                   => $qScore->criteria_scores,
            ]);
        }
    }

    return response()->json(['message' => 'Assessment and scores saved successfully.']);
}

    public function mySavedAssessments(Request $request)
    {
        $query = Assessment::where('teacher_id', Auth::id());

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhere('question_type', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $savedAssessments = $query->latest()->get();
        

        return view('SavedAssessment', compact('savedAssessments'));
    }


    public function destroy($id)
    {
        $assessment = Assessment::with('questions')->find($id);

        if (!$assessment) {
            return response()->json([
                'message' => 'Assessment not found!'
            ], 404);
        }

         $title = $assessment->title;

        // Delete related questions first
        $assessment->questions()->delete();

        // Now delete the assessment
        $assessment->delete();

        ActivityLogger::log(
            "Deleted Assessment",
            "Assessment '{$title}' deleted with all related questions."
        );

        return response()->json([
            'message' => 'Assessment deleted successfully!'
        ]);
    }

    public function savedprevdestroy($id)
    {
        $assessment = Assessment::with('questions')->find($id);

        if (!$assessment) {
            return response()->json([
                'message' => 'Assessment not found!'
            ], 404);
        }

        // Delete related questions first
        $assessment->questions()->delete();

        // Now delete the assessment
        $assessment->delete();

        return response()->json([
            'message' => 'Assessment deleted successfully!'
        ]);
    }

    public function saveddestroy($id)
    {
        try {
            $assessment = Assessment::with('questions')->find($id);

            if (!$assessment) {
                return response()->json([
                    'message' => 'Assessment not found!'
                ], 404);
            }

            $title = $assessment->title;

            // Delete related questions first
            $assessment->questions()->delete();

            // Then delete the assessment
            $assessment->delete();

            ActivityLogger::log(
                "Deleted Assessment",
                "Assessment '{$title}' deleted with all related questions."
            );

            return response()->json([
                'message' => 'Assessment deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting assessment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $assessment = Assessment::with('questions')->findOrFail($id);

        $teacher = auth()->guard('web')->user(); // or use the correct guard
        $classes = $teacher->classes; // assuming you have a 'classes' relationship on Teacher model

        return view('saved-assessment-preview', compact('assessment', 'classes'));
    }


    public function showAssessmentForm()
    {
        $questionTypes = QuestionType::all();
        return view('GenerateAssessment', compact('questionTypes'));
    }

    public function showScores(Assessment $assessment)
    {
        $scores = StudentAssessmentScore::with([
            'student.classes',    // ← eager‑load both
            'questionScores'
        ])
        ->where('assessment_id', $assessment->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return view('savedscores', compact('assessment', 'scores'));
    }

    public function viewSavedResult($id)
    {

        try {
           $score = StudentAssessmentScore::with([
                'student',
                'questionScores.question',   // ← correct nested relationship
                'assessment.questions'       // ← lowercase 'assessment' as defined in your model
            ])->findOrFail($id);
          
            return view('saved-scoring-result-preview', [
                'score' => $score,
                'assessment' => $score->Assessment
            ]);            
        } catch (\Exception $e) {
            Log::error("Scoring result error", ['id' => $id, 'error' => $e->getMessage()]);
            abort(404);
        }
    }

    public function scoredestroy($id)
    {
        try {
            $assessment = StudentAssessmentScore::with('questionScores')->find($id);
    
            if (!$assessment) {
                return response()->json([
                    'message' => 'Assessment not found!'
                ], 404);
            }
    
            // Delete related question scores first
            $assessment->questionScores()->delete();
    
            // Then delete the assessment
            $assessment->delete();
    
            return response()->json([
                'message' => 'Score deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting assessment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function updateTime(Request $request, $id)
    {
        $request->validate([
            'due_date' => 'required|date',
            'time_limit' => 'nullable|integer|min:1',
        ]);

        $assignment = AssessmentClass::findOrFail($id);

        $assignment->due_date = $request->due_date;
        $assignment->time_limit = $request->time_limit;
        $assignment->save();

        ActivityLogger::log(
            "Updated Assessment Assignment",
            "Updated Assignment ID {$assignment->id} → New Due Date: {$request->due_date}, Time Limit: {$request->time_limit} mins"
        );

        return response()->json([
            'message' => 'Updated successfully.',
            'due_date_formatted' => $assignment->due_date ? $assignment->due_date->format('F d, Y') : 'N/A',
            'time_limit_formatted' => $assignment->time_limit ? $assignment->time_limit . ' mins' : 'N/A'
        ]);

    }

    public function destroyTime($id)
    {
        $assignment = AssessmentClass::find($id);

        if (!$assignment) {
            return response()->json([
                'message' => 'Assigned assessment not found.'
            ], 404);
        }

        try {
            $assignment->delete();

            ActivityLogger::log(
                "Deleted Assigned Assessment",
                "Assignment ID {$id} deleted."
            );

            return response()->json([
                'message' => 'Assigned assessment deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
