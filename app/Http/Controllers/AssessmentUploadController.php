<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\SchoolClass;

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

        $assessment = Assessment::findOrFail($validated['assessment_id']);

        $assessment->assignedClasses()->syncWithoutDetaching([
            $validated['school_class_id'] => [
                'time_limit' => $validated['time_limit'],
                'due_date' => $validated['due_date'], // ✅ Include due_date
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assessment uploaded to class successfully!'
        ]);
    }

}
