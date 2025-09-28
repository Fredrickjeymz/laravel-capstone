<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;
use App\Helpers\ActivityLogger;
use App\Mail\TeacherCredentialsMail;
use Illuminate\Support\Facades\Mail;
use App\Helpers\BrevoMailer;
use Illuminate\Support\Str;

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

        // OPTIONAL: quick MX check (uncomment to enable)
        // $domain = substr(strrchr($validated['email'], "@"), 1);
        // if (!checkdnsrr($domain, 'MX')) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Email domain does not accept mail. Please verify the email.'
        //     ], 422);
        // }

        // Generate a secure random password (includes symbols)
        $rawPassword = $this->generateSecurePassword(12); // length 12 (change if you want)
        $hashedPassword = Hash::make($rawPassword);

        // Save teacher
        $teacher = Teacher::create([
            'fname'               => $validated['fname'],
            'mname'               => $validated['mname'] ?? null,
            'lname'               => $validated['lname'],
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'birthdate'           => $validated['birthdate'],
            'position'            => $validated['position'],
            'gender'              => $validated['gender'],
            'username'            => $validated['email'],
            'password'            => $hashedPassword,
        ]);

        ActivityLogger::log(
            "Created Educator",
            "Educator Name: {$teacher->fname} {$teacher->mname} {$teacher->lname}"
        );

        // Send email with credentials (synchronous). If you set up queues, use ->queue(...) instead of ->send(...)
        // Send email with credentials via Brevo API
        try {
            $mailer = new BrevoMailer();
            $mailer->send(
                $teacher->email,
                "{$teacher->fname} {$teacher->lname}",
                "Your Educator Account Credentials",
                "<p>Hello <b>{$teacher->fname}</b>,</p>
                <p>Your account has been created successfully.</p>
                <p><b>Username(email):</b> {$teacher->username}<br>
                <b>Password:</b> {$rawPassword}</p>
                <p>You can now log in to the system.</p>
                <p>For security, please log in and change your password immediately. This temporary password will only be valid until you reset it.</p>
                <p>Best regards,<br>Admin Team</p>"
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'teacher' => $teacher,
                'warning' => 'Teacher created but email delivery failed. Check Brevo configuration.',
                'error'   => $e->getMessage(), // Optional, remove in prod
            ], 201);
        }
    }

    /**
     * Generate a secure random password containing upper, lower, digits and symbols.
     *
     * @param int $length
     * @return string
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // avoid ambiguous letters if you want
        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $digits = '23456789';
        $symbols = '!@#$%^&*()-_=+[]{}<>?';

        // ensure at least one of each category
        $password = [];
        $password[] = $upper[random_int(0, strlen($upper) - 1)];
        $password[] = $lower[random_int(0, strlen($lower) - 1)];
        $password[] = $digits[random_int(0, strlen($digits) - 1)];
        $password[] = $symbols[random_int(0, strlen($symbols) - 1)];

        $all = $upper . $lower . $digits . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);
        return implode('', $password);
    }

}
