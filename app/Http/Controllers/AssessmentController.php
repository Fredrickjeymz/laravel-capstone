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
        $assessment = Assessment::find($id);

        if (!$assessment) {
            return response()->json(['status' => 'not_found']);
        }

        return response()->json([
            'status' => $assessment->status,
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

    public function updateAssessment(Request $request)
    {
        try {
            $assessment = Assessment::findOrFail($request->assessment_id);

            /* UPDATE BASIC FIELDS */
            if (!empty($request->fields)) {
                foreach ($request->fields as $field => $value) {
                    if (in_array($field, $assessment->getFillable())) {
                        $assessment->$field = $value;
                    }
                }
                $assessment->save();
            }

            /* UPDATE QUESTIONS & OPTIONS */
            if (!empty($request->questions)) {
                foreach ($request->questions as $q) {
                    AssessmentQuestion::where('id', $q['id'])
                        ->where('assessment_id', $assessment->id)
                        ->update([
                            'question_text' => $q['question_text']
                        ]);
                }
            }

            /* UPDATE MCQ OPTIONS (embedded in question_text) */
            if (!empty($request->answers)) {
                // Group by question_id to handle multiple options per question
                $optionsByQuestion = [];
                foreach ($request->answers as $a) {
                    if ($a['type'] === 'option') {
                        $qId = $a['question_id'];
                        if (!isset($optionsByQuestion[$qId])) {
                            $optionsByQuestion[$qId] = [];
                        }
                        $optionsByQuestion[$qId][$a['option_label']] = $a['option_text'];
                    }
                }

                // Update MCQ options
                foreach ($optionsByQuestion as $questionId => $optionsMap) {
                    $question = AssessmentQuestion::where('id', $questionId)
                        ->where('assessment_id', $assessment->id)
                        ->first();

                    if ($question) {
                        $currentText = $question->question_text;
                        
                        // Extract current question (without options)
                        preg_match('/^(.*?)(?=\s+[A-Z]\))/s', $currentText, $q_match);
                        $questionPart = trim($q_match[1] ?? $currentText);

                        // Parse existing options
                        preg_match_all('/\s+([A-Z])\)\s+(.*?)(?=\s+[A-Z]\)|$)/s', $currentText, $matches);
                        $existingOptions = [];
                        if (!empty($matches[1])) {
                            foreach ($matches[1] as $idx => $letter) {
                                $existingOptions[$letter] = trim($matches[2][$idx]);
                            }
                        }

                        // Merge with updated options
                        $allOptions = array_merge($existingOptions, $optionsMap);
                        ksort($allOptions); // Sort A, B, C, D...

                        // Rebuild question_text
                        $rebuiltText = $questionPart;
                        foreach ($allOptions as $letter => $text) {
                            $rebuiltText .= " " . $letter . ") " . $text;
                        }

                        // Also rebuild options column for backup/evaluation purposes
                        $optionsColumn = '';
                        foreach ($allOptions as $letter => $text) {
                            $optionsColumn .= $letter . ") " . $text . " ";
                        }

                        $question->update([
                            'question_text' => trim($rebuiltText),
                            'options' => trim($optionsColumn)  // ← Also update options column
                        ]);
                    }
                }

                // Update direct answer keys (non-MCQ)
                foreach ($request->answers as $a) {
                    if ($a['type'] === 'direct') {
                        AssessmentQuestion::where('id', $a['question_id'])
                            ->where('assessment_id', $assessment->id)
                            ->update([
                                'answer_key' => $a['answer_key']
                            ]);
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Assessment updated successfully']);

        } catch (\Exception $e) {
            Log::error('Assessment update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
