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
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
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
        ]);

        $credentials = $request->only('username', 'password');

        $user = null;
        $guard = null;
        $redirect = null;

        // 🔹 Check in Teachers
        $teacher = \App\Models\Teacher::where('username', $request->username)->first();
        if ($teacher) {
            $guard = 'web';
            $redirect = route('teacherdashboard');
            $user = $teacher;

            // Check default password
            $birthYear = \Carbon\Carbon::parse($teacher->birthdate)->year;
            $defaultPassword = $teacher->lname . '_' . $birthYear;
            if (Hash::check($defaultPassword, $teacher->password)) {
                $redirect = route('teacher.change-password');
            }
        }

        // 🔹 Check in Students (only if not teacher)
        if (!$user) {
            $student = \App\Models\Student::where('username', $request->username)->first();
            if ($student) {
                $guard = 'student';
                $redirect = route('stud-dash');
                $user = $student;

                $birthYear = \Carbon\Carbon::parse($student->birthdate)->year;
                $defaultPassword = $student->lname . '_' . $birthYear;
                if (Hash::check($defaultPassword, $student->password)) {
                    $redirect = route('student.change-password');
                }
            }
        }

        // 🔹 Check in Admins (only if not teacher/student)
        if (!$user) {
            $admin = \App\Models\Admin::where('username', $request->username)->first();
            if ($admin) {
                $guard = 'admin';
                $redirect = route('admindashboard');
                $user = $admin;
            }
        }

        // If username not found in any table
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        // 🔹 Attempt login with correct guard
        if (Auth::guard($guard)->attempt($credentials)) {
            session(['auth_guard' => $guard]);

            return response()->json([
                'success'  => true,
                'redirect' => $redirect,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid username or password.'
        ], 401);
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
                <p><b>Username:</b> {$teacher->username}<br>
                <b>Password:</b> {$rawPassword}</p>
                <p>You can now log in to the system.</p>
                <p>For security, please log in and change your password immediately. This temporary password will only be valid until you reset it.</p>
                <p>Best regards,<br>Admin Team</p>"
            );
        } catch (\Exception $e) {

        }

        return response()->json([
            'success' => true,
            'teacher' => $teacher,
            'message' => 'Teacher created successfully. Credentials sent to email.'
        ], 201);
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

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Try both teachers and students
        $user = \App\Models\Teacher::where('email', $request->email)->first()
            ?? \App\Models\Student::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email.'
            ], 404);
        }

        // Generate a reset token
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Reset link
        $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($user->email);

        // Send via your BrevoMailer
        $mailer = new BrevoMailer();
        $mailer->send(
            $user->email,
            $user->fname . ' ' . $user->lname,
            "Reset Your Password",
            "<p>Hello <b>{$user->fname}</b>,</p>
            <p>You requested a password reset. Click below to reset:</p>
            <p><a href='{$resetUrl}'>Reset Password</a></p>
            <p>If you didn’t request this, ignore this email.</p>"
        );

        return response()->json([
            'success' => true,
            'message' => 'A reset link has been sent to your email.'
        ]);
    }

    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email'); 

        return view('reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get reset record
        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Find user in either teachers or students
        $user = \App\Models\Teacher::where('email', $request->email)->first()
            ?? \App\Models\Student::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return back()->with('success', 'Your password has been reset successfully! You may now return to your email or close this page.');
        
    }


}
