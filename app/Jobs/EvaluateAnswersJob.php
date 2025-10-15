<?php

namespace App\Jobs;

use App\Models\Assessment;
use App\Models\StudentAssessmentScore;
use App\Models\StudentAssessmentQuestionScore;
use App\Helpers\ActivityLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvaluateAnswersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $assessment;
    public $student;
    public $submittedAnswers;

    /**
     * Create a new job instance.
     */
    public function __construct($assessment, $student, $submittedAnswers)
    {
        $this->assessment = $assessment;
        $this->student = $student;
        $this->submittedAnswers = $submittedAnswers;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        try {
            Log::info("🚀 Starting AI evaluation for student {$this->student->full_name}");

            // === Build prompt exactly like yours ===
            $prompt = "Evaluate student answers against the following assessment:\n\n";
            $prompt .= "- For multiple choice questions, answer_key must complete (e.g. A. Laravel).\n";
            $prompt .= "ASSESSMENT TITLE: {$this->assessment->title}\n";
            $prompt .= "QUESTION TYPE: {$this->assessment->question_type}\n\n";

            foreach ($this->assessment->questions as $q) {
                $prompt .= "QUESTION {$q->sequence_number}:\n{$q->question_text}\n";
                $prompt .= "CORRECT ANSWER: {$q->answer_key}\n\n";
            }

            if ($this->assessment->rubric) {
                $prompt .= "RUBRIC:\n{$this->assessment->rubric}\n\n";
            }

            $prompt .= "STUDENT ANSWERS:\n";
            foreach ($this->submittedAnswers as $questionId => $answer) {
                $prompt .= "Question ID {$questionId}: {$answer}\n";
            }

            $prompt .= "\nINSTRUCTIONS:\n";
            $prompt .= "- Return JSON with total_score, max_score, percentage, question_results, and overall_feedback.\n";
            $prompt .= "- Each question_results[] must have student_answer, score, max_score, feedback.\n";
            $prompt .= "- For multiple choice questions, student_answer must complete (e.g. A. Laravel).\n";
            $prompt .= "- For subjective: include rubric-based criteria_scores.\n";
            $prompt .= "- Also return a short (1 sentence) overall_feedback summarizing the student's performance.\n";
            $prompt .= "- JSON only. No extra explanation. Do NOT include markdown like ```json.\n\n";
            $prompt .= "JSON ONLY OUTPUT:";

            // === Send request ===
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])
                ->timeout(120)
                ->connectTimeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a strict but fair teacher evaluating student work.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ]);

            if ($response->failed()) {
                throw new \Exception("OpenAI API failed with status " . $response->status());
            }

            $content = $response->json('choices.0.message.content');
            if (!$content) throw new \Exception("AI returned empty content.");

            $content = trim($content);
            $content = preg_replace('/^
    (?:json)?\s*([\s\S]*?)\s*
    $/', '$1', $content);
            $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content);

            $result = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON: " . json_last_error_msg());
            }

            // === Save results ===
            $score = StudentAssessmentScore::create([
                'student_id' => $this->student->id,
                'assessment_id' => $this->assessment->id,
                'total_score' => $result['total_score'],
                'max_score' => $result['max_score'],
                'percentage' => $result['percentage'],
                'remarks' => $result['overall_feedback'] ?? null,
                'status' => 'completed',
            ]);

            $questions = $this->assessment->questions->values();
            foreach ($result['question_results'] as $index => $r) {
                if (!isset($questions[$index])) continue;

                StudentAssessmentQuestionScore::create([
                    'student_assessment_score_id' => $score->id,
                    'assessment_question_id' => $questions[$index]->id,
                    'student_answer' => $r['student_answer'] ?? '',
                    'score_given' => $r['score'] ?? 0,
                    'max_score' => $r['max_score'] ?? 1,
                    'criteria_scores' => isset($r['criteria_scores']) ? json_encode($r['criteria_scores']) : null,
                    'feedback' => $r['feedback'] ?? null,
                ]);
            }

            ActivityLogger::log(
                'AI Evaluation Completed',
                "{$this->student->full_name} - {$this->assessment->title} scored {$score->total_score}/{$score->max_score} ({$score->percentage}%)"
            );

            Log::info("✅ Evaluation completed successfully for student {$this->student->full_name}");

            // Store the score result in a class property for access
            $this->evaluationResult = $score;

        } catch (\Exception $e) {
            Log::error("❌ AI Evaluation failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // Add a getter method to retrieve the result
    public function getEvaluationResult()
    {
        return $this->evaluationResult ?? null;
    }

}
