<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use App\Models\Student;

class ChangePassController extends Controller
{
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_pass' => 'required',
            'new_pass' => 'required|min:6|confirmed', // requires new_pass_confirmation field
        ]);

        // ✅ Get the currently authenticated teacher (via web guard)
        $teacher = Auth::guard('web')->user();

        if (!$teacher) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // ✅ Verify current password
        if (!Hash::check($request->input('current_pass'), $teacher->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // ✅ Update password securely
        /** @var Teacher $teacher */
        $teacher->password = Hash::make($request->input('new_pass'));
        $teacher->save();

        return response()->json([
            'message' => 'Password successfully changed',
            'redirect' => route('teacherdashboard') // make sure this route exists
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
        ]);

        $teacher = Auth::guard('web')->user(); // current logged-in teacher

        if (!$teacher) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // Update fields
         /** @var Teacher $teacher */
        $teacher->fname = $request->fname;
        $teacher->mname = $request->mname;
        $teacher->lname = $request->lname;
        $teacher->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'redirect' => route('teacherdashboard')
        ]);
    }

    public function StudentchangePassword(Request $request)
    {
        $request->validate([
            'current_pass' => 'required',
            'new_pass' => 'required|min:6|confirmed', // requires new_pass_confirmation field
        ]);

        // ✅ Get the currently authenticated teacher (via web guard)
        $student = Auth::guard('student')->user();

        if (!$student) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // ✅ Verify current password
        if (!Hash::check($request->input('current_pass'), $student->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // ✅ Update password securely
        /** @var Student $student */
        $student->password = Hash::make($request->input('new_pass'));
        $student->save();

        return response()->json([
            'message' => 'Password successfully changed',
            'redirect' => route('stud-dash') // make sure this route exists
        ]);
    }

    public function StudentupdateProfile(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
        ]);

        $student = Auth::guard('student')->user(); // current logged-in teacher

        if (!$student) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // Update fields
         /** @var Student $student */
        $student->fname = $request->fname;
        $student->mname = $request->mname;
        $student->lname = $request->lname;
        $student->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'redirect' => route('stud-dash')
        ]);
    }
}
