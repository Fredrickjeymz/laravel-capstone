<?php

namespace App\Http\Controllers;
use App\Models\ArchivedAssessment;
use App\Models\ArchivedAssessmentQuestion;
use Illuminate\Support\Facades\DB;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class ArchiveAssessment extends Controller
{
    public function archiveAssessment($id)
    {
        DB::transaction(function () use ($id) {
            // Find the assessment
            $assessment = Assessment::with('questions')->findOrFail($id);

            // Copy parent
            $archivedAssessment = ArchivedAssessment::create([
                'id' => $assessment->id,
                'teacher_id' => $assessment->teacher_id,
                'title' => $assessment->title,
                'subject' => $assessment->subject,
                'instructions' => $assessment->instructions,
                'question_type' => $assessment->question_type,
                'rubric' => $assessment->rubric,
            ]);

            // Copy all questions
            foreach ($assessment->questions as $question) {
                ArchivedAssessmentQuestion::create([
                    'archived_assessment_id' => $archivedAssessment->id,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                    'answer_key' => $question->answer_key,
                    'sequence_number' => $question->sequence_number,
                ]);
            }

            // Delete child questions first
            $assessment->questions()->delete();

            // Then delete parent assessment
            $assessment->delete();
            ActivityLogger::log(
                "Archived Assessment",
                "Assessment Title: {$assessment->title}"
        );
        });

        return response()->json(['success' => true, 'message' => 'Assessment archived successfully.']);
    }

    public function restoreAssessment($id)
    {
        DB::transaction(function () use ($id) {
            // Fetch archived assessment and its questions
            $archived = ArchivedAssessment::with('questions')->findOrFail($id);
    
            // Save questions temporarily in memory
            $archivedQuestions = $archived->questions->toArray();
    
            // Restore parent assessment
            $assessment = Assessment::create([
                'teacher_id' => $archived->teacher_id,
                'title' => $archived->title,
                'subject' => $archived->subject,
                'instructions' => $archived->instructions,
                'question_type' => $archived->question_type,
                'rubric' => $archived->rubric,
            ]);
    
            // Restore each question
            foreach ($archivedQuestions as $question) {
                AssessmentQuestion::create([
                    'assessment_id' => $assessment->id,
                    'question_text' => $question['question_text'],
                    'options' => $question['options'],
                    'answer_key' => $question['answer_key'],
                    'sequence_number' => $question['sequence_number'],
                ]);
            }
    
            // Now safe to delete archived after restoring
            $archived->questions()->delete();
            $archived->delete();
            ActivityLogger::log(
                "Restored Assessment",
                "Assessment Title: {$assessment->title}"
        );
        });
    
        return response()->json(['success' => true, 'message' => 'Assessment restored successfully.']);
    }
    

    public function deleteArchivedAssessment($id)
    {
        DB::transaction(function () use ($id) {
            $archived = ArchivedAssessment::with('questions')->findOrFail($id);

            // Keep title before deleting
            $title = $archived->title;

            // Delete all archived questions
            $archived->questions()->delete();

            // Delete the archived assessment
            $archived->delete();

            // Log the action
            ActivityLogger::log(
                "Deleted Archived Assessment",
                "Assessment Title: {$title}"
            );
        });

        return response()->json(['success' => true, 'message' => 'Archived assessment deleted successfully.']);
    }


    
}
