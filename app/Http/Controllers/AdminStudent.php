<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendStudentCredentials;

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
            'lrn'      => 'required|digits:12|unique:students,lrn',
            'fname'    => 'required|string|max:255',
            'mname'    => 'nullable|string|max:255',
            'lname'    => 'required|string|max:255',
            'email'    => 'required|email|unique:students,email',
            'gender'   => 'required|in:male,female',
            'birthdate'=> 'required|date',
        ]);

        // Username = email
        $username = $validated['email'];

        // Generate random strong password
        $rawPassword = Str::random(10); // e.g. "aZ9#qW8pL2"
        $hashedPassword = Hash::make($rawPassword);

        // Save student
        $student = Student::create([
            'lrn'      => $validated['lrn'],
            'fname'    => $validated['fname'],
            'mname'    => $validated['mname'] ?? null,
            'lname'    => $validated['lname'],
            'email'    => $validated['email'],
            'username' => $username,
            'password' => $hashedPassword,
            'gender'   => $validated['gender'],
            'birthdate'=> $validated['birthdate'],
        ]);

        ActivityLogger::log("Created Student", "Student Name: {$request->fname} {$request->mname} {$request->lname}");

        // Send credentials to student via email
        Mail::to($student->email)->send(new SendStudentCredentials($student->username, $rawPassword));

        return response()->json([
            'success'  => true,
            'data'     => $student,
            'message'  => 'Student created successfully. Credentials sent to email.'
        ]);
    }
}
