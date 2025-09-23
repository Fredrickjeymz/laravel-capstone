<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\SchoolClass;
use App\Helpers\ActivityLogger;
use App\Notifications\QuizUploaded;

class AssessmentUploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'assessment_id'     => 'required|exists:assessments,id',
            'school_class_id'   => 'required|exists:school_classes,id',
            'time_limit'        => 'required|integer|min:1',
            'due_date'          => 'required|date|after:now',
        ]);

        $teacher = auth('web')->user();
        $class = \App\Models\SchoolClass::find($validated['school_class_id']);

        $assessment = Assessment::findOrFail($validated['assessment_id']);

        $assessment->assignedClasses()->syncWithoutDetaching([
            $validated['school_class_id'] => [
                'time_limit' => $validated['time_limit'],
                'due_date' => $validated['due_date'], // ✅ Include due_date
            ]
        ]);

        $class->students->each(function($student) use ($class, $teacher, $assessment) {
            $student->notify(new QuizUploaded($class, $teacher, $assessment));
        });

        // ✅ Log activity
        ActivityLogger::log(
            "Uploaded Assessment",
            "Assessment '{$assessment->title}' uploaded to Class ID {$validated['school_class_id']} with Due Date: {$validated['due_date']}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Assessment uploaded to class successfully!'
        ]);
    }

}
