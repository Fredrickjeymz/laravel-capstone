<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ActivityLogger;

class AdminStudent extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query(); // start a query builder, not all()

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(fname, ' ', mname, ' ', lname) LIKE ?", ["%{$search}%"])
                ->orWhere('lrn', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('gender', 'like', "%{$search}%")
                ->orWhere('birthdate', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->get(); // run the query

        return view('Admin-Students', compact('students'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lrn' => 'required|digits:12',
            'fname'     => 'required|string|max:255',
            'mname'     => 'nullable|string|max:255',
            'lname'     => 'required|string|max:255',
            'email'     => 'required|email|unique:students,email',
            'gender'    => 'required|in:male,female',
            'birthdate' => 'required|date', // now required
        ]);

        // username = email
        $username = $validated['email'];

        // always generate password as lastname_birthyear
        $birthYear = Carbon::parse($validated['birthdate'])->year;
        $password  = $validated['lname'] . '_' . $birthYear;

        $student = Student::create([
            'lrn'     => $validated['lrn'],
            'fname'     => $validated['fname'],
            'mname'     => $validated['mname'] ?? null,
            'lname'     => $validated['lname'],
            'email'     => $validated['email'],
            'username'  => $username,
            'password'  => Hash::make($password),
            'gender'    => $validated['gender'],
            'birthdate' => $validated['birthdate'],
        ]);

        ActivityLogger::log("Created Student", "Student Name: {$request->fname} {$request->mname} {$request->lname}");

        return response()->json([
            'success'            => true,
            'data'               => $student,
            'generated_username' => $username,
            'generated_password' => $password, // ⚠️ plaintext — only for dev/demo
        ]);
    }
}
