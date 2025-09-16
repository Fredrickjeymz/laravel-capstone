<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('AdminLogIn');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            Auth::guard('admin')->login($admin);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('admindashboard')
                ]);
            }

            return redirect()->route('admindashboard')->with('success', 'Login successful!');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        return back()->withErrors(['username' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        if ($request->ajax()) {
            return response()->json(['redirect' => route('login')]);
        }

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

 
}
