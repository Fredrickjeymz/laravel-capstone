<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ArchivedTeacher;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
   public function TeacherIndex(Request $request)
    {
        $query = Teacher::query(); 

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(fname, ' ', mname, ' ', lname) LIKE ?", ["%{$search}%"])
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('gender', 'like', "%{$search}%")
                ->orWhere('birthdate', 'like', "%{$search}%");
            });
        }

        $teachers = $query->latest()->get();

        return view('TeachersManagement', compact('teachers'));
    }

    public function ArchivedTeacherIndex(Request $request)
    {

        $query = ArchivedTeacher::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(fname, ' ', mname, ' ', lname) LIKE ?", ["%{$search}%"])
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%")
                ->orWhere('gender', 'like', "%{$search}%")
                ->orWhere('birthdate', 'like', "%{$search}%");
            });
        }

        $teachers = $query->latest()->get();

        return view('Archive', compact('teachers'));
    }
}
