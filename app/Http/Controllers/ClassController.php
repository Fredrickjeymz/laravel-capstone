<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolClass;

class ClassController extends Controller
{
    public function index()
    {
        $teacher = auth()->guard('web')->user(); // using the 'web' guard (Teacher)

        $classes = SchoolClass::withCount('students')->where('teacher_id', $teacher->id)->get();

        return view('Classes', compact('classes'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_name'  => 'required|string|max:255',
            'subject'     => 'nullable|string|max:255',
            'year_level'  => 'required|string|max:255',
        ]);

        // Ensure the teacher is authenticated
        $teacher = auth()->guard('web')->user();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only authenticated teachers can create classes.'
            ], 403);
        }

        $class = \App\Models\SchoolClass::create([
            'class_name'  => $validated['class_name'],
            'subject'     => $validated['subject'],
            'year_level'  => $validated['year_level'],
            'teacher_id'  => $teacher->id,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $class,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'year_level' => 'required|in:Grade 7,Grade 8,Grade 9,Grade 10',
        ]);

        $class = \App\Models\SchoolClass::findOrFail($id);

        $class->update([
            'class_name' => $request->class_name,
            'subject' => $request->subject,
            'year_level' => $request->year_level,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => url()->previous()
        ]);
    }

        public function destroy($id)
    {
        $type = SchoolClass::findOrFail($id);
        $type->delete();

        return response()->json(['success' => true]);
    }

}
