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
use App\Jobs\EvaluateAnswersJob;
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
        
        // ✅ FIX: Check if ANY score exists (even with 0 points = processing)
        $alreadyTaken = $studentScore !== null; // This should now work!

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
        'assessments' => $sortedAssessments
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
        $classId = $pivot?->school_class_id;

        return view('StudentTakeQuiz', compact('assessment', 'time_limit', 'class', 'classId'));
    }

    public function evaluateAnswers(Request $request)
    {
        $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'answers' => 'required|array',
        ]);

        $student = auth()->guard('student')->user();
        $assessment = Assessment::with('questions')->findOrFail($request->assessment_id);
        $submittedAnswers = $request->answers;
        $class_id = $request->input('class_id');

        try {
            // Try synchronous evaluation first
            set_time_limit(120); // 2 minutes max
            
            $evaluator = new EvaluateAnswersJob($assessment, $student, $submittedAnswers, $class_id);
            $evaluator->handle();
            
            $score = $evaluator->getEvaluationResult();

            if ($score) {
                return response()->json([
                    'message' => '✅ Evaluation completed successfully!',
                    'status' => 'completed',
                    'score' => $score->total_score,
                    'max_score' => $score->max_score,
                    'percentage' => $score->percentage,
                    'remarks' => $score->remarks,
                    'evaluation_id' => $score->id
                ]);
            }

        } catch (\Exception $e) {
            Log::warning("Sync evaluation failed, falling back to async: " . $e->getMessage());
            
            $class_id = $request->input('class_id');
            // Fallback to async processing
            StudentAssessmentScore::create([
                'student_id' => $student->id,
                'assessment_id' => $assessment->id,
                'class_id' => $class_id,
                'total_score' => 0,
                'max_score' => 100,
                'percentage' => 0,
                'remarks' => 'AI evaluation in progress...',
                'status' => 'processing',
            ]);

            EvaluateAnswersJob::dispatch($assessment, $student, $submittedAnswers, $class_id);

            return response()->json([
                'message' => '✅ Submission received! AI evaluation in progress.',
                'status' => 'queued'
            ]);
        }
    }
    
}

