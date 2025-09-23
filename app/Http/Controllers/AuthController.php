<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;
use App\Helpers\ActivityLogger;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('LogIn'); // Ensure this view exists
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:teacher,student,admin',
        ]);

        $credentials = $request->only('username', 'password');

        $guard = null;
        $redirect = null;

        switch ($request->role) {
            case 'teacher':
                $guard = 'web'; // teacher guard
                $redirect = route('teacherdashboard');

                // 🔹 Check if still using default password
                $user = \App\Models\Teacher::where('username', $request->username)->first();
                if ($user) {
                    $birthYear = \Carbon\Carbon::parse($user->birthdate)->year;
                    $defaultPassword = $user->lname . '_' . $birthYear;

                    if (Hash::check($defaultPassword, $user->password)) {
                        // ✅ Force redirect to change password page
                        $redirect = route('teacher.change-password');
                    }
                }
                break;

            case 'student':
                $guard = 'student';
                $redirect = route('stud-dash');
                $user = \App\Models\Student::where('username', $request->username)->first();
                if ($user) {
                    $birthYear = \Carbon\Carbon::parse($user->birthdate)->year;
                    $defaultPassword = $user->lname . '_' . $birthYear;

                    if (Hash::check($defaultPassword, $user->password)) {
                        // ✅ Force redirect to change password page
                        $redirect = route('student.change-password');
                    }
                }
                break;

            case 'admin':
                $guard = 'admin';
                $redirect = route('admindashboard');
                break;
        }

        if (Auth::guard($guard)->attempt($credentials)) {
            // 🔹 Save which guard is used, so logout can use it
            session(['auth_guard' => $guard]);

            if ($request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'redirect' => $redirect,
                ]);
            }

            return redirect($redirect)->with('success', 'Login successful!');
        }

        // If login fails
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        return back()->withErrors(['username' => 'Invalid credentials.']);
    }


    // Logout function
    public function logout(Request $request)
    {
        $guard = session('auth_guard', 'web'); // default to teacher/web if missing

        Auth::guard($guard)->logout();

        $request->session()->forget('auth_guard');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax()) {
            return response()->json(['redirect' => route('login')]);
        }

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fname'     => 'required|string|max:100',
            'mname'     => 'nullable|string|max:100',
            'lname'     => 'required|string|max:100',
            'email'     => 'required|email|unique:teachers,email',
            'phone'     => 'required|string|max:20',
            'birthdate' => 'required|date',
            'position'  => 'required|string',
            'gender'    => 'required|string|in:Male,Female,Other',
        ]);

        // Generate username and password
        $username = $validated['email'];
        $birthYear = Carbon::parse($validated['birthdate'])->year;
        $rawPassword = $validated['lname'] . '_' . $birthYear;   // e.g. Santos_1990
        $hashedPassword = Hash::make($rawPassword);

        // Save teacher
        $teacher = Teacher::create([
            'fname'     => $validated['fname'],
            'mname'     => $validated['mname'] ?? null,
            'lname'     => $validated['lname'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'],
            'birthdate' => $validated['birthdate'],
            'position'  => $validated['position'],
            'gender'    => $validated['gender'],
            'username'  => $username,
            'password'  => $hashedPassword,
        ]);

        ActivityLogger::log("Created Educator", "Educator Name: {$request->fname} {$request->mname} {$request->lname}");

        // Return full teacher data + generated password (for dev/demo)
        return response()->json([
            'teacher'   => $teacher,
            'username'  => $username,
            'password'  => $rawPassword, // ⚠️ only return this for demo/dev
        ]);
    }

}
