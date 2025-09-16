<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssessmentType;
use App\Models\QuestionType;

class QuestionTypeController extends Controller
{
   public function QuestionTypeRestore(Request $request)
    {
        $query = QuestionType::with('assessmentType');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('typename', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('assessmenttype_id', 'like', "%{$search}%");
            });
        }

        $questiontype = $query->get(); 
        $assessmenttypes = AssessmentType::all();

        return view('QuestionTypes', compact('questiontype', 'assessmenttypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'typename' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assessmenttype_id' => 'required|exists:assessment_types,id',
        ]);
    
        $questionType = QuestionType::create([
            'typename' => $request->typename,
            'description' => $request->description,
            'assessmenttype_id' => $request->assessmenttype_id,
        ]);
    
        return response()->json(['success' => true, 'data' => $questionType]);
    }    

    public function update(Request $request, $id)
    {
        $request->validate([
            'typename' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assessmenttype_id' => 'required|integer',
        ]);
    
        $type = QuestionType::findOrFail($id);
    
        $type->update([
            'typename' => $request->typename,
            'description' => $request->description,
            'assessmenttype_id' => $request->assessmenttype_id
        ]);
    
        return response()->json(['success' => true]);
    }
    
    public function destroy($id)
    {
        $type = QuestionType::findOrFail($id);
        $type->delete();

        return response()->json(['success' => true]);
    }



}
