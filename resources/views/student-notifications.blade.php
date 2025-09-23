@extends('StudentMainLayout')

@section('content-area')
<div id="student-content-area">
    <div class="top">
        <h2>Notifications</h2>
        <p>Check what’s new and relevant to you.</p>
    </div>
        <div class="notification-container">
            @if($notifications->isEmpty())
                <div class="no-notifications">No notifications yet.</div>
            @else
                @foreach($notifications as $notification)
                    <div class="notification-card {{ $notification->read_at ? 'read' : 'unread' }}">
                        <div class="notification-header">
                            <span class="notification-title">{{ $notification->data['title'] }}</span>
                            <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="notification-body">
                            <p>{{ $notification->data['message'] }}</p>
                            @if(isset($notification->data['class_name']))
                                <p><strong>Class:</strong> {{ $notification->data['class_name'] }} ({{ $notification->data['year_level'] }})</p>
                            @endif
                            @if(isset($notification->data['subject']))
                                <p><strong>Subject:</strong> {{ $notification->data['subject'] }}</p>
                            @endif
                            @if(isset($notification->data['teacher']))
                                <p><strong>By:</strong> {{ $notification->data['teacher'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
</div>
@endsection