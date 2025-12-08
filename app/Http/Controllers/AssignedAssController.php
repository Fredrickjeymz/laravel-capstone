<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Assessment;
use App\Models\AssessmentClass;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Models\StudentAssessmentQuestionScore;
use Illuminate\Support\Str;
use App\Helpers\ActivityLogger;

class AssignedAssController extends Controller
{

    public function itemAnalysis($assessment_id, $class_id)
    {
        $teacher = auth()->guard('web')->user();

        $assessment = \App\Models\Assessment::with(['questions', 'studentScores.questionScores'])
            ->where('teacher_id', $teacher->id)
            ->findOrFail($assessment_id);

        $class = \App\Models\SchoolClass::with('students')->findOrFail($class_id);

        $students = $class->students;
        $totalStudents = $students->count();

        $itemAnalysis = [];

        foreach ($assessment->questions as $question) {
            // Load only scores for this question, for the given class and assessment
            $questionScores = StudentAssessmentQuestionScore::where('assessment_question_id', $question->id)
                ->whereHas('studentAssessmentScore', function ($q) use ($class_id, $assessment) {
                    $q->where('class_id', $class_id)
                    ->where('assessment_id', $assessment->id);
                })->get();

            $totalRespondents = $questionScores->count();

            // initialize
            $correct = 0;
            $incorrect = 0;
            $avgScorePercent = null; // for subjective

            // Normalize answer_key for objective comparison
            $answerKeyRaw = $question->answer_key; // may be string or JSON
            $normalizedAnswerKey = null;
            if (is_array($answerKeyRaw)) {
                $normalizedAnswerKey = array_map(function($v){ return Str::lower(trim((string)$v)); }, $answerKeyRaw);
            } else {
                $normalizedAnswerKey = Str::lower(trim((string)$answerKeyRaw));
            }

            // Determine question type heuristic: if answer_key exists -> objective; otherwise subjective
            $isObjective = !is_null($question->answer_key) && $question->answer_key !== '';

            if ($totalRespondents > 0) {
                if ($isObjective) {
                    foreach ($questionScores as $qs) {
                        $studentAnsRaw = $qs->student_answer;

                        // normalize student's answer(s)
                        if (is_array($studentAnsRaw)) {
                            $normalizedStudentAns = array_map(function($v){ return Str::lower(trim((string)$v)); }, $studentAnsRaw);
                        } else {
                            $normalizedStudentAns = Str::lower(trim((string)$studentAnsRaw));
                        }

                        // Compare: handle single value or array keys
                        $isCorrect = false;
                        if (is_array($normalizedAnswerKey)) {
                            // key may be array of acceptable answers
                            if (is_array($normalizedStudentAns)) {
                                // any overlap -> correct
                                $isCorrect = count(array_intersect($normalizedAnswerKey, $normalizedStudentAns)) > 0;
                            } else {
                                $isCorrect = in_array($normalizedStudentAns, $normalizedAnswerKey, true);
                            }
                        } else {
                            // single key
                            if (is_array($normalizedStudentAns)) {
                                $isCorrect = in_array($normalizedAnswerKey, $normalizedStudentAns, true);
                            } else {
                                $isCorrect = $normalizedStudentAns === $normalizedAnswerKey;
                            }
                        }

                        if ($isCorrect) $correct++;
                    }

                    $incorrect = $totalRespondents - $correct;
                    $percentage = $totalRespondents > 0 ? round(($correct / $totalRespondents) * 100, 2) : 0;

                } else {
                    // Subjective: compute average score percentage from score_given and max_score (per response)
                    $totalPercent = 0;
                    $countForAvg = 0;
                    foreach ($questionScores as $qs) {
                        // ensure max_score exists on the student score row, otherwise fallback
                        $max = $qs->max_score ?? 0;
                        $given = $qs->score_given ?? 0;
                        if ($max > 0) {
                            $pct = ($given / $max) * 100;
                            $totalPercent += $pct;
                            $countForAvg++;
                        }
                    }
                    $avgScorePercent = $countForAvg > 0 ? round($totalPercent / $countForAvg, 2) : 0;

                    // For mastery classification we can treat >= 75% as 'correct' for item-level mastery if desired
                    $percentage = $avgScorePercent;
                    // optionally set correct = number of respondents >= 75% of max
                    $correct = $questionScores->filter(function($qs){
                        $max = $qs->max_score ?? 0; $given = $qs->score_given ?? 0;
                        return $max > 0 && ($given / $max) >= 0.75;
                    })->count();
                    $incorrect = $totalRespondents - $correct;
                }
            } else {
                $percentage = 0;
                $correct = 0;
                $incorrect = 0;
            }

            // Difficulty and Mastery based on percentage (you asked for M / NYM / NM)
            // difficulty & mastery based on your new ranges
            if ($percentage >= 96) {
                $mastery = 'Highly Mastered'; 
                $difficulty = 'Very Easy'; 
                $cause = 'Concept fully mastered';
            } elseif ($percentage >= 86) {
                $mastery = 'Mastered'; 
                $difficulty = 'Easy'; 
                $cause = 'Concept mastered';
            } elseif ($percentage >= 66) {
                $mastery = 'Nearly Mastered'; 
                $difficulty = 'Average'; 
                $cause = 'Partial understanding';
            } elseif ($percentage >= 35) {
                $mastery = 'Average Mastered'; 
                $difficulty = 'Average'; 
                $cause = 'Needs reinforcement';
            } elseif ($percentage >= 15) {
                $mastery = 'Low Mastered'; 
                $difficulty = 'Difficult'; 
                $cause = 'Lacks understanding';
            } else {
                $mastery = 'Not Mastered'; 
                $difficulty = 'Very Difficult'; 
                $cause = 'Complete misunderstanding';
            }

            // choose which examinees count to use: respondents or class size
            // I recommend respondents (students who actually answered)
            $itemAnalysis = [];
            $totalCorrectAll = 0;

            // Determine number_of_examinees: use respondents across the assessment (unique students who submitted)
            $allQuestionScores = \App\Models\StudentAssessmentQuestionScore::whereHas('studentAssessmentScore', function($q) use ($class_id, $assessment) {
                $q->where('class_id', $class_id)->where('assessment_id', $assessment->id);
            })->get();

            // Unique examinees who made at least one response
            $examineeIds = $allQuestionScores->pluck('student_assessment_score_id')->unique()->map(function($id){
                // resolve student_assessment_score -> student_id for uniqueness
                $sas = \App\Models\StudentAssessmentScore::find($id);
                return $sas ? $sas->student_id : null;
            })->filter()->unique()->values();

            $numberOfExaminees = $examineeIds->count();

            // fallback to class size if no respondents (optional)
            if ($numberOfExaminees === 0) {
                $numberOfExaminees = $totalStudents;
            }

            foreach ($assessment->questions as $question) {
                // scores for this question filtered by class & assessment
                $questionScores = \App\Models\StudentAssessmentQuestionScore::where('assessment_question_id', $question->id)
                    ->whereHas('studentAssessmentScore', function ($q) use ($class_id, $assessment) {
                        $q->where('class_id', $class_id)
                        ->where('assessment_id', $assessment->id);
                    })->get();

                $totalRespondents = $questionScores->pluck('student_assessment_score_id')
                    ->unique()->count(); // respondents for this item

                // compute correct (objective) or "correct by threshold" (subjective)
                $correct = 0;
                $avgScorePercent = null;

                $answerKeyRaw = $question->answer_key;
                $normalizedAnswerKey = is_array($answerKeyRaw)
                    ? array_map(fn($v) => \Illuminate\Support\Str::lower(trim((string)$v)), $answerKeyRaw)
                    : \Illuminate\Support\Str::lower(trim((string)$answerKeyRaw));

                $isObjective = !is_null($question->answer_key) && $question->answer_key !== '';

                if ($questionScores->count() > 0) {
                    if ($isObjective) {
                        foreach ($questionScores as $qs) {
                            $studentAnsRaw = $qs->student_answer;
                            $normalizedStudentAns = is_array($studentAnsRaw)
                                ? array_map(fn($v) => \Illuminate\Support\Str::lower(trim((string)$v)), $studentAnsRaw)
                                : \Illuminate\Support\Str::lower(trim((string)$studentAnsRaw));

                            $isCorrect = false;
                            if (is_array($normalizedAnswerKey)) {
                                if (is_array($normalizedStudentAns)) {
                                    $isCorrect = count(array_intersect($normalizedAnswerKey, $normalizedStudentAns)) > 0;
                                } else {
                                    $isCorrect = in_array($normalizedStudentAns, $normalizedAnswerKey, true);
                                }
                            } else {
                                if (is_array($normalizedStudentAns)) {
                                    $isCorrect = in_array($normalizedAnswerKey, $normalizedStudentAns, true);
                                } else {
                                    $isCorrect = $normalizedStudentAns === $normalizedAnswerKey;
                                }
                            }

                            if ($isCorrect) $correct++;
                        }
                    } else {
                        // subjective: average percent and count those >= 75% as 'correct' optionally
                        $totalPercent = 0; $countForAvg = 0;
                        foreach ($questionScores as $qs) {
                            $max = $qs->max_score ?? 0;
                            $given = $qs->score_given ?? 0;
                            if ($max > 0) {
                                $pct = ($given / $max) * 100;
                                $totalPercent += $pct;
                                $countForAvg++;
                                if (($given / $max) >= 0.75) $correct++;
                            }
                        }
                        $avgScorePercent = $countForAvg > 0 ? round($totalPercent / $countForAvg, 2) : 0;
                    }
                }

                // per-item measures relative to number of examinees (use overall examinee count)
                $item_mean = $numberOfExaminees > 0 ? round($correct / $numberOfExaminees, 4) : 0; // correct per examinee
                $item_mps = $numberOfExaminees > 0 ? round($item_mean * 100, 2) : 0; // percent for this item

                // per-item percentage among respondents (keep for info)
                $percentage = $totalRespondents > 0 ? round(($correct / $totalRespondents) * 100, 2) : 0;
                $incorrect = $totalRespondents - $correct;

                // difficulty & mastery (same rules)
                // difficulty & mastery based on your new ranges
                if ($item_mps >= 96) {
                    $mastery = 'Highly Mastered'; 
                    $difficulty = 'Very Easy'; 
                    $cause = 'Concept fully mastered';
                } elseif ($item_mps >= 86) {
                    $mastery = 'Mastered'; 
                    $difficulty = 'Easy'; 
                    $cause = 'Concept mastered';
                } elseif ($item_mps >= 66) {
                    $mastery = 'Nearly Mastered'; 
                    $difficulty = 'Average'; 
                    $cause = 'Partial understanding';
                } elseif ($item_mps >= 35) {
                    $mastery = 'Average Mastered'; 
                    $difficulty = 'Average'; 
                    $cause = 'Needs reinforcement';
                } elseif ($item_mps >= 15) {
                    $mastery = 'Low Mastered'; 
                    $difficulty = 'Difficult'; 
                    $cause = 'Lacks understanding';
                } else {
                    $mastery = 'Not Mastered'; 
                    $difficulty = 'Very Difficult'; 
                    $cause = 'Complete misunderstanding';
                }

                $totalCorrectAll += $correct;

                $teachername = $assessment->teacher->fname . ' '. ' ' . $assessment->teacher->mname . ' ' . $assessment->teacher->lname;
                $teacherposition = $assessment->teacher->position;

                $itemAnalysis[] = [
                    'question' => $question->question_text,
                    'correct' => $correct,
                    'incorrect' => $incorrect,
                    'total_respondents' => $totalRespondents,
                    'number_of_examinees' => $numberOfExaminees,
                    'item_mean' => $item_mean,
                    'item_mps' => $item_mps,
                    'percentage' => $percentage,
                    'difficulty' => $difficulty,
                    'mastery_level' => $mastery,
                    'cause' => $cause,
                    'avg_score_percent' => $avgScorePercent,
                    'teachername' => $teachername,
                    'teacherposition' => $teacherposition
                ];
            }

            $totalItems = count($itemAnalysis);
            $numberOfExaminees = $itemAnalysis[0]['number_of_examinees'] ?? 0;
            $totalCorrectAll = array_sum(array_column($itemAnalysis, 'correct'));

            // avoid division by zero
            if ($numberOfExaminees == 0 || $totalItems == 0) {
                $mean = 0;
                $mps = 0;
            } else {
                // DepEd-standard mean
                $mean = $totalCorrectAll / $numberOfExaminees;

                // MPS = (mean ÷ total items) × 100
                $mps = ($mean / $totalItems) * 100;
            }

            // 🚨 UPDATE THESE ARRAYS WITH YOUR NEW MASTERY LEVELS:
            $masteryCount = [
                'Highly Mastered' => 0,
                'Mastered' => 0,
                'Nearly Mastered' => 0,
                'Average Mastered' => 0,
                'Low Mastered' => 0,
                'Not Mastered' => 0
            ];

            $difficultyCount = [
                'Very Easy' => 0,
                'Easy' => 0,
                'Average' => 0,
                'Difficult' => 0,
                'Very Difficult' => 0
            ];

            foreach ($itemAnalysis as $item) {
                $masteryCount[$item['mastery_level']]++;
                $difficultyCount[$item['difficulty']]++;
            }

            // ----- Generate interpretation text -----

            // Mastery Trend - UPDATE THESE CONDITIONS TOO:
            if (($masteryCount['Highly Mastered'] + $masteryCount['Mastered']) / $totalItems >= 0.60) {
                $masteryStatement = "Learners have demonstrated strong mastery of the competencies.";
            } elseif (($masteryCount['Nearly Mastered'] + $masteryCount['Average Mastered']) / $totalItems >= 0.50) {
                $masteryStatement = "Learners show partial mastery, indicating the need for reinforcement activities.";
            } else {
                $masteryStatement = "Learners exhibit low mastery, requiring reteaching and targeted remediation.";
            }

            // Difficulty Trend
            if ($difficultyCount['Difficult'] > $difficultyCount['Easy']) {
                $difficultyStatement = "A high number of items were difficult, suggesting misalignment or insufficient coverage during instruction.";
            } elseif ($difficultyCount['Average'] > $difficultyCount['Easy']) {
                $difficultyStatement = "Most items fall under average difficulty, showing balanced but improvable performance.";
            } else {
                $difficultyStatement = "Most items were easy, indicating that students generally understood the lesson well.";
            }

            // MPS Interpretation
            if ($mps >= 75) {
                $mpsRemark = "The class met the expected mastery level.";
            } elseif ($mps >= 50) {
                $mpsRemark = "The class shows partial mastery but did not reach the expected proficiency.";
            } else {
                $mpsRemark = "The class did not meet the expected mastery level and requires remediation.";
            }

            // Final feedback block
            $overallFeedback = "
            Overall, the class achieved an MPS of " . number_format($mps, 2) . "%.  
            $masteryStatement  
            $difficultyStatement  
            $mpsRemark
            ";
        }

        return view('itemAnalysis', compact('assessment', 'class', 'itemAnalysis', 'teacher', 'overallFeedback', 'mean', 'mps', 'totalCorrectAll', 'totalRespondents'));
    }

    public function AssignedAss(Request $request)
    {
        $teacher = auth()->guard('web')->user();

        // Get assessments assigned by this teacher
        $assignedAss = AssessmentClass::with([
            'assessment' => function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                    ->withCount('questions')
                    ->with('teacher');
            },
            'class.students', // For total students in class
            'assessment.studentScores', // For answered count
        ])->get();

        $now = now(); // For status logic

        return view('AssignedAssessments', compact('assignedAss', 'now'));
    }

    public function viewStudents($classId)
    {
        $class = SchoolClass::with('students')->findOrFail($classId);
        
        // Get all students NOT in this class for the datalist
        $allStudents = \App\Models\Student::whereDoesntHave('classes', function($query) use ($classId) {
            $query->where('school_class_id', $classId);
        })->get();

        return view('students-in-class', [
            'class' => $class,
            'students' => $class->students,
            'allStudents' => $allStudents // Students available to add
        ]);
    }

    public function addToClass(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_class_id' => 'required|exists:school_classes,id',
        ]);

        $class = \App\Models\SchoolClass::find($validated['school_class_id']);
        $student = \App\Models\Student::find($validated['student_id']);

        // Check if already assigned
        if ($class->students()->where('student_id', $validated['student_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Student is already assigned to this class.'
            ], 409);
        }

        $class->students()->attach($validated['student_id']);
        $student->load('classes');

        // Log activity and send notification
        $teacher = auth('web')->user();
        ActivityLogger::log(
            "Added Student to Class",
            "Student: {$student->fname} {$student->lname} was added to {$class->class_name} by Teacher: {$teacher->fname} {$teacher->lname}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Student successfully assigned to the class.',
            'student' => $student
        ]);
    }

    public function removeStudent(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:school_classes,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $class = SchoolClass::findOrFail($request->input('class_id'));

        // ✅ Use the teacher guard explicitly
        $teacherId = auth('web')->id(); // or: Auth::guard('teacher')->id()

        if (!$teacherId) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // ✅ Compare IDs as ints
        if ((int) $class->teacher_id !== (int) $teacherId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // ✅ Detach the student from this class (pivot: class_student)
        $class->students()->detach($request->input('student_id'));

        // 📝 Log
        $student = \App\Models\Student::find($request->input('student_id'));
        ActivityLogger::log(
            "Removed Student from Class",
            "Student: {$student->fname} {$student->lname}, Class: {$class->class_name} ({$class->year_level})"
        );


        return response()->json(['success' => true]);
    }
}
