<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index()
    {
        $teacher = Auth::guard('web')->user();

        // Fetch logs only for the logged-in teacher
        $logs = ActivityLog::where('user_id', $teacher->id)
            ->where('user_type', 'Teacher') // ✅ so admins don’t show up
            ->latest()
            ->get();

        return view('teacher-activity-log', compact('logs'));
    }

    public function indexadmin()
    {
        // ✅ Fetch ALL logs
        $logs = ActivityLog::orderBy('created_at', 'desc')
            ->latest()
            ->get();

        return view('admin-activity-log', compact('logs'));
    }

    public function indexstudent()
    {
        $teacher = Auth::guard('student')->user();

        // Fetch logs only for the logged-in teacher
        $logs = ActivityLog::where('user_id', $teacher->id)
            ->where('user_type', 'Student') // ✅ so admins don’t show up
            ->latest()
            ->get();

        return view('student-activity-log', compact('logs'));
    }

    public function indexnotification(Request $request)
    {
        $student = auth('student')->user();

        // Fetch all notifications
        $notifications = $student->notifications()->latest()->get();

        // Mark as read
        $student->unreadNotifications->markAsRead();

        return view('student-notifications', compact('notifications'));
    }


}

