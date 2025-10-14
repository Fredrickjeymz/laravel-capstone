<?php

namespace App\Jobs;

use App\Models\StudentAssessmentScore;
use App\Models\StudentAssessmentQuestionScore;
use App\Models\Assessment;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStudentEvaluation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scoreId;
    protected $answers;

    public function __construct($scoreId, $answers)
    {
        $this->scoreId = $scoreId;
        $this->answers = $answers;
    }

    public function handle()
    {
        $score = StudentAssessmentScore::find($this->scoreId);
        if (!$score) return;

        $assessment = Assessment::with('questions')->find($score->assessment_id);

        try {
            $prompt = "Evaluate student answers for assessment: {$assessment->title}\n\n";
            foreach ($assessment->questions as $q) {
                $prompt .= "Q: {$q->question_text}\nA: {$q->answer_key}\n\n";
            }
            $prompt .= "STUDENT ANSWERS:\n";
            foreach ($this->answers as $qid => $ans) {
                $prompt .= "Question ID {$qid}: {$ans}\n";
            }
            $prompt .= "Return JSON with total_score, max_score, percentage, and question_results.";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $json = json_decode($response->json('choices.0.message.content'), true);

            $score->update([
                'total_score' => $json['total_score'] ?? 0,
                'max_score' => $json['max_score'] ?? count($assessment->questions),
                'percentage' => $json['percentage'] ?? 0,
                'remarks' => $json['overall_feedback'] ?? 'Graded',
                'status' => 'completed'
            ]);

            foreach ($json['question_results'] as $index => $qres) {
                $question = $assessment->questions[$index] ?? null;
                if (!$question) continue;

                StudentAssessmentQuestionScore::create([
                    'student_assessment_score_id' => $score->id,
                    'assessment_question_id' => $question->id,
                    'student_answer' => $qres['student_answer'] ?? '',
                    'score_given' => $qres['score'] ?? 0,
                    'max_score' => $qres['max_score'] ?? 1,
                    'feedback' => $qres['feedback'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Evaluation job failed: ' . $e->getMessage());
            $score->update(['status' => 'failed']);
        }
    }
}

