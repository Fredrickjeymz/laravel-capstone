<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action, $description = null)
    {
        $user = null;
        $userType = 'Guest';

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $userType = 'Admin';
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $userType = 'Teacher';
        } elseif (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
            $userType = 'Student';
        }

        ActivityLog::create([
            'user_id'    => $user?->id,
            'user_type'  => $userType,
            'action'     => $action,
            'description'=> $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
