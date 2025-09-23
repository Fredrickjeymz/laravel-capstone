<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Assessment;
use App\Models\StudentAssessmentScore;
use App\Models\Student;
use App\Models\StudentAssessmentQuestionScore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class QuizViewController extends Controller
{
    public function showQuizzes($id)
    {
        $student = auth()->guard('student')->user();

        $class = SchoolClass::with([
            'assessments' => function ($query) use ($student) {
                $query->withCount('questions')
                    ->with(['studentScores' => function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    }])
                    ->withPivot('time_limit', 'due_date', 'created_at');
            },
            'teacher'
        ])->findOrFail($id);

        // ✅ Make sure student belongs to class
        if (!$student->classes->contains('id', $class->id)) {
            abort(403, 'Unauthorized access to this class');
        }

        $now = now()->timezone(config('app.timezone'));

        $sortedAssessments = $class->assessments->sortBy(function ($assessment) use ($student, $now) {
            $studentScore = $assessment->studentScores->firstWhere('student_id', $student->id);
            $alreadyTaken = $studentScore !== null;

            $dueDateRaw = $assessment->pivot->due_date ?? null;
            $dueDate = $dueDateRaw
                ? \Carbon\Carbon::parse($dueDateRaw)->timezone(config('app.timezone'))
                : null;

            $isDue = $dueDate && $dueDate->isPast();

            // Priority system
            if ($alreadyTaken) {
                $priority = 2; // completed last
            } elseif ($isDue) {
                $priority = 1; // overdue middle
            } else {
                $priority = 0; // pending first
            }

            // Sort by priority first, then due date
            return [$priority, $dueDate ?? $now->copy()->addYears(10)];
        });

        return view('InsideClass', [
            'class' => $class,
            'student' => $student,
            'now' => $now,
            'assessments' => $sortedAssessments // ✅ use this in Blade
        ]);
    }


    public function showAllQuizzes()
    {
        $student = auth()->guard('student')->user();

        // Get class IDs student is in
        $classIds = $student->classes()->pluck('school_class_id')->toArray();

        // Fetch classes with assessments that have the pivot
        $classes = \App\Models\SchoolClass::with([
            'assessments' => function ($query) {
                $query->withCount('questions')
                    ->with('teacher')
                    ->withPivot('time_limit', 'due_date', 'created_at');
            },
            'assessments.studentScores' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            },
            'teacher'
        ])
        ->whereIn('id', $classIds)
        ->get();

        $now = now();

        return view('StudentQuiz', compact('classes', 'student', 'now'));
    }

    public function show($assessment_id)
    {
        $student = auth()->guard('student')->user();
        $studentClassIds = $student->classes()->pluck('school_class_id')->toArray();

        // Get the pivot record
        $pivot = DB::table('assessment_class')
            ->where('assessment_id', $assessment_id)
            ->whereIn('school_class_id', $studentClassIds)
            ->first();

        $assessment = Assessment::with('questions')->findOrFail($assessment_id);
        $time_limit = $pivot?->time_limit ?? 30;

        // ✅ Load the related class
        $class = \App\Models\SchoolClass::with('teacher')->find($pivot?->school_class_id);

        return view('StudentTakeQuiz', compact('assessment', 'time_limit', 'class'));
    }


public function evaluateAnswers(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'answers' => 'required|array'
        ]);

        try {
            $student = auth()->guard('student')->user(); // ✅ Ensure you're using the correct guard
            $assessment = Assessment::with('questions')->findOrFail($request->assessment_id);
            $submittedAnswers = $request->answers;

            $evaluationData = [
                'assessment' => $assessment,
                'student_answers' => $submittedAnswers,
                'question_type' => $assessment->question_type
            ];

            $aiResponse = $this->callEvaluationAI($evaluationData);

            if (
                !isset($aiResponse['total_score'], $aiResponse['max_score'], $aiResponse['percentage'], $aiResponse['question_results']) ||
                !is_array($aiResponse['question_results'])
            ) {
                throw new \Exception("Incomplete or invalid AI response.");
            }

            $score = StudentAssessmentScore::create([
                'student_id' => $student->id,
                'assessment_id' => $assessment->id,
                'total_score' => $aiResponse['total_score'],
                'max_score' => $aiResponse['max_score'],
                'percentage' => $aiResponse['percentage'],
                'remarks' => $aiResponse['overall_feedback'] ?? null
            ]);

            $questions = $assessment->questions->values();

            foreach ($aiResponse['question_results'] as $index => $result) {
                if (!isset($questions[$index])) continue;

                StudentAssessmentQuestionScore::create([
                    'student_assessment_score_id' => $score->id,
                    'assessment_question_id' => $questions[$index]->id,
                    'student_answer' => $result['student_answer'] ?? '',
                    'score_given' => $result['score'] ?? 0,
                    'max_score' => $result['max_score'] ?? 1,
                    'criteria_scores' => isset($result['criteria_scores']) ? json_encode($result['criteria_scores']) : null,
                    'feedback' => $result['feedback'] ?? null,
                ]);
            }

            ActivityLogger::log(
                'Submitted Answer',
                "{$student->full_name} submitted answers for '{$assessment->title}' 
                - Score: {$score->total_score}/{$score->max_score} ({$score->percentage}%)."
            );

            return response()->json([
                'message' => 'Quiz submitted successfully.',
                'score' => $score,
            ]);


        } catch (\Exception $e) {
            Log::error('Evaluation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Evaluation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function callEvaluationAI($data)
    {
        Log::info('Building AI evaluation prompt');

        $prompt = "Evaluate student answers against the following assessment:\n\n";
        $prompt .= "- For multiple choice questions, answer_key must complete (e.g. A. Laravel).\n";
        $prompt .= "ASSESSMENT TITLE: {$data['assessment']->title}\n";
        $prompt .= "QUESTION TYPE: {$data['question_type']}\n\n";

        foreach ($data['assessment']->questions as $q) {
            $prompt .= "QUESTION {$q->sequence_number}:\n{$q->question_text}\n";
            $prompt .= "CORRECT ANSWER: {$q->answer_key}\n\n";
        }

        if ($data['assessment']->rubric) {
            $prompt .= "RUBRIC:\n{$data['assessment']->rubric}\n\n";
        }

        $prompt .= "STUDENT ANSWERS:\n";
        foreach ($data['student_answers'] as $questionId => $answer) {
            $prompt .= "Question ID {$questionId}: {$answer}\n";
        }

        $prompt .= "\nINSTRUCTIONS:\n";
        $prompt .= "- Return JSON with total_score, max_score, percentage, question_results, and overall_feedback.\n";
        $prompt .= "- Each question_results[] must have student_answer, score, max_score, feedback.\n";
        $prompt .= "- For multiple choice questions, student_answer must complete (e.g. A. Laravel).\n";
        $prompt .= "- For subjective: include rubric-based criteria_scores.\n";
        $prompt .= "- Also return an overall_feedback summarizing the student's performance.\n";
        $prompt .= "- JSON only. No extra explanation. Do NOT include markdown like ```json.\n\n";
        $prompt .= "JSON ONLY OUTPUT:";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
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

            if (!$content) {
                throw new \Exception("AI returned empty content.");
            }

            $content = trim($content);
            $content = preg_replace('/^```(?:json)?\s*([\s\S]*?)\s*```$/', '$1', $content);
            $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content);

            Log::info('Sanitized AI content', ['content' => $content]);

            $result = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON parsing failed', [
                    'error' => json_last_error_msg(),
                    'content' => $content
                ]);
                throw new \Exception("Invalid JSON: " . json_last_error_msg());
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('AI call failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
}

