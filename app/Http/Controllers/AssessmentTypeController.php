<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\AssessmentType;

class AssessmentTypeController extends Controller
{
    public function AssessmentTypeRestore()
    {
        $assessmenttype = AssessmentType::all(); // fetch all teacher records
        return view('AssessmentTypes', compact('assessmenttype'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:assessment_types,id',
            'description' => 'required|string'
        ]);
    
        $type = AssessmentType::findOrFail($request->id);
        $type->update([
            'description' => $request->description,
        ]);
    
        return response()->json(['success' => true, 'message' => 'Updated']);
    }
    
    
}
