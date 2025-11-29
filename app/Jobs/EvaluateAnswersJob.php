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
    public $class_id;

    /**
     * Create a new job instance.
     */
    public function __construct($assessment, $student, $submittedAnswers, $class_id)
    {
        $this->assessment = $assessment;
        $this->student = $student;
        $this->submittedAnswers = $submittedAnswers;
         $this->class_id = $class_id;
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

            // 🔥 ENHANCED: More specific instructions for multiple choice
            if ($this->assessment->question_type === 'Multiple Choice') {
                $prompt .= "CRITICAL FOR MULTIPLE CHOICE:\n";
                $prompt .= "- Student answers MUST be compared as LETTERS ONLY (A, B, C, D)\n";
                $prompt .= "- Correct answers are LETTERS ONLY\n";
                $prompt .= "- If student wrote text, map it to the corresponding letter\n";
                $prompt .= "- Score: 1 if letters match, 0 if they don't\n\n";
            } elseif ($this->assessment->question_type === 'True Or False') {
                $prompt .= "CRITICAL FOR TRUE/FALSE:\n";
                $prompt .= "- Student answers MUST be compared as True/False only\n";
                $prompt .= "- Normalize variations (e.g., 'T', 'true', 'TRUE' → 'True')\n\n";
            }

            $prompt .= "ASSESSMENT TITLE: {$this->assessment->title}\n";
            $prompt .= "QUESTION TYPE: {$this->assessment->question_type}\n\n";

            foreach ($this->assessment->questions as $q) {
                $prompt .= "QUESTION {$q->sequence_number}:\n{$q->question_text}\n";
                
                // 🔥 ENHANCED: Show answer format explicitly
                if ($this->assessment->question_type === 'Multiple Choice') {
                    $prompt .= "CORRECT ANSWER (LETTER): {$q->answer_key}\n\n";
                } else {
                    $prompt .= "CORRECT ANSWER: {$q->answer_key}\n\n";
                }
            }

            if ($this->assessment->rubric) {
                $prompt .= "RUBRIC:\n{$this->assessment->rubric}\n\n";
            }

            $prompt .= "STUDENT ANSWERS:\n";
            foreach ($this->submittedAnswers as $questionId => $answer) {
                $prompt .= "Question ID {$questionId}: {$answer}\n";
            }

            $prompt .= "- Return JSON with question_results and overall_feedback.\n";
            if (!in_array($this->assessment->question_type, ['Essay', 'Short Answer Questions', 'Critically Thought-out Opinions'])) {
                $prompt .= "- For each question, return a 'score' of 1 if correct, 0 if incorrect.\n";
                $prompt .= "- For each question, return 'student_answer' with the normalized student answer.\n";
            }
            $prompt .= "- I will calculate total_score, max_score, and percentage myself.\n";

            // 🔥 ENHANCED: More specific formatting rules
            if ($this->assessment->question_type === 'Multiple Choice') {
                $prompt .= "- For multiple choice: student_answer must be the LETTER ONLY (e.g., 'A', 'B', 'C', 'D').\n";
                $prompt .= "- For multiple choice: score is 1 if letters match exactly, 0 otherwise.\n";
                $prompt .= "- Map student text answers to corresponding letters (e.g., 'Mike De Leon' → 'C').\n";
            } elseif ($this->assessment->question_type === 'True Or False') {
                $prompt .= "- For True/False: student_answer must be 'True' or 'False' only.\n";
                $prompt .= "- For True/False: score is 1 if answers match exactly, 0 otherwise.\n";
                $prompt .= "- Normalize student answers (e.g., 'T' → 'True', 'F' → 'False').\n";
            } // For subjective questions ONLY - use your proven approach
            elseif (in_array($this->assessment->question_type, ['Essay', 'Short Answer Questions', 'Critically Thought-out Opinions'])) {
                $prompt .= "- You MUST evaluate using the rubric provided.\n";
                $prompt .= "- For EACH QUESTION, convert every rubric criteria into a numeric score.\n";
                $prompt .= "- The score must be computed using its weight. Example:\n";
                $prompt .= "    If a criterion has weight 30%, return a max_score of 30.\n";
                $prompt .= "- You MUST return this EXACT JSON structure for every question:\n";
                $prompt .= "  \"criteria_scores\": [\n";
                $prompt .= "       { \"criteria\": \"Content & Development\", \"score\": number, \"max_score\": 30 },\n";
                $prompt .= "       { \"criteria\": \"Organization\", \"score\": number, \"max_score\": 20 },\n";
                $prompt .= "       { \"criteria\": \"Grammar and Mechanics\", \"score\": number, \"max_score\": 20 },\n";
                $prompt .= "       { \"criteria\": \"Critical Thinking\", \"score\": number, \"max_score\": 20 },\n";
                $prompt .= "       { \"criteria\": \"Clarity & Coherence\", \"score\": number, \"max_score\": 10 }\n";
                $prompt .= "  ]\n";
                $prompt .= "- DO NOT return qualitative ratings only. You MUST return numeric scores.\n";
                $prompt .= "- Without criteria_scores with numeric values, the evaluation is INVALID.\n";
                $prompt .= "- Also return an overall_feedback for the entire assessment as 'overall_feedback' key outside of 'question_results'.\n";
                $prompt .= "- JSON only, valid parseable JSON, no extra text or markdown.\n";
            } else {
                // For objectives - use strict matching (your current approach)
                $prompt .= "- Compare answers exactly or with minor spelling variations only.\n";
                $prompt .= "- Score is 1 if answers match exactly, 0 otherwise.\n";
                $prompt .= "- JSON only, valid parseable JSON, no extra text or markdown.\n";
            }

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

            // === Calculate scores ourselves ===
            $totalScore = 0;
            $maxScore = 0;

            // Check if this is a subjective assessment
            $isSubjective = in_array($this->assessment->question_type, ['Essay', 'Short Answer Questions', 'Critically Thought-out Opinions']);

            if ($isSubjective) {
                // 🔥 FOR SUBJECTIVE: Use criteria_scores with max_score = 100
                $totalPercentage = 0;
                $questionCount = 0;
                
                foreach ($result['question_results'] as $index => $r) {
                    if (isset($r['criteria_scores']) && is_array($r['criteria_scores'])) {
                        $questionTotal = 0;
                        $criteriaCount = 0;
                        
                        foreach ($r['criteria_scores'] as $criteria) {
                            $score = $criteria['score'] ?? 0;
                            $max = $criteria['max_score'] ?? 1;
                            $questionTotal += ($max > 0) ? ($score / $max) * 100 : 0;
                            $criteriaCount++;
                        }
                        
                        if ($criteriaCount > 0) {
                            $questionPercentage = $questionTotal / $criteriaCount;
                            $totalPercentage += $questionPercentage;
                            $questionCount++;
                        }
                    }
                }
                
                $percentage = $questionCount > 0 ? $totalPercentage / $questionCount : 0;
                $totalScore = $percentage;
                $maxScore = 100; // Fixed at 100 for subjective

                $subj_remarks = $result['overall_feedback'] ?? $this->generateSubjectiveRemarks($percentage);
                
            } else {
                // 🔥 FOR OBJECTIVE: Count correct answers
                $questionCount = count($this->assessment->questions);
                foreach ($result['question_results'] as $index => $r) {
                    $questionScore = $r['score'] ?? 0;
                    $totalScore += $questionScore; // Each correct answer = 1 point
                }
                $maxScore = $questionCount; // Max = total questions
                $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;
            }

            // === Save results ===
            $score = StudentAssessmentScore::create([
                'student_id' => $this->student->id,
                'assessment_id' => $this->assessment->id,
                'class_id' => $this->class_id,
                'total_score' => $totalScore, // ✅ We calculate this
                'max_score' => $maxScore, // ✅ We calculate this
                'percentage' => $percentage, // ✅ We calculate this
                'remarks' => $result['overall_feedback'] ?? $subj_remarks ?? '',
                'status' => 'completed',
            ]);

            $questions = $this->assessment->questions->values();
            foreach ($result['question_results'] as $index => $r) {
                if (!isset($questions[$index])) continue;

                if (in_array($this->assessment->question_type, [
                    'Essay',
                    'Short Answer Questions',
                    'Critically Thought-out Opinions'
                ])) {

                    // 1. Student's raw answer from submitted form
                    $r['student_answer'] = $this->submittedAnswers[$questions[$index]->id] ?? '';

                    // 2. Compute numeric score from criteria_scores
                    if (isset($r['criteria_scores']) && is_array($r['criteria_scores'])) {

                        $totalPercent = 0;
                        $count = 0;

                        foreach ($r['criteria_scores'] as $crit) {
                            if (isset($crit['score']) && isset($crit['max_score'])) {
                                $totalPercent += ($crit['score'] / $crit['max_score']) * 100;
                                $count++;
                            }
                        }

                        // Final numeric score (0–100)
                        $r['score'] = $count > 0 ? $totalPercent / $count : 0;
                        $r['max_score'] = 100; // subjective always 100 scale
                    }
                }

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

    /**
 * Generate remarks for subjective questions based on percentage
 */
    private function generateSubjectiveRemarks(float $percentage): string
    {
        if ($percentage >= 90) {
            return "Outstanding writing! Demonstrates excellent critical thinking, organization, and clarity.";
        } elseif ($percentage >= 80) {
            return "Strong writing skills with good organization and clear reasoning. Minor areas for refinement.";
        } elseif ($percentage >= 70) {
            return "Good effort with clear ideas. Focus on developing more depth and improving organization.";
        } elseif ($percentage >= 60) {
            return "Adequate response. Work on strengthening arguments and improving writing clarity.";
        } else {
            return "Needs improvement in organization, clarity, and depth of analysis. Review the rubric criteria.";
        }
    }

}
