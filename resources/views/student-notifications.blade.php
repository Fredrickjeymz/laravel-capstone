@extends('StudentMainLayout')

@section('content-area')
<div id="student-content-area">
    <div class="top">
        <h2>Notifications</h2>
        <p>Check what’s new and relevant to you.</p>
    </div>
<div class="notifications-container">
    @if($notifications->isEmpty())
        <div class="empty-card">
            <p>No notifications yet.</p>
        </div>
    @else
        <div class="notifications-scroll">
            <div class="notifications-list">
                @foreach($notifications as $notification)
                    <article class="notification-card {{ $notification->read_at ? 'read' : 'unread' }}">
                        <!-- Left side: Date/Time -->
                        <div class="notification-left">
                            <div class="notification-date">{{ $notification->created_at->format('M d, Y') }}</div>
                            <div class="notification-time">{{ $notification->created_at->format('h:i A') }}</div>
                        </div>

                        <!-- Main content -->
                        <div class="notification-main">
                            <div class="notification-title">
                                <strong>{{ $notification->data['title'] }}</strong>
                            </div>

                            <div class="notification-desc">
                                {{ $notification->data['message'] }}
                            </div>

                            <div class="notification-sub">
                                @if(isset($notification->data['class_name']))
                                    <span>Class: {{ $notification->data['class_name'] }} ({{ $notification->data['year_level'] }})</span>
                                @endif
                                @if(isset($notification->data['subject']))
                                    <span>Subject: {{ $notification->data['subject'] }}</span>
                                @endif
                                @if(isset($notification->data['teacher']))
                                    <span>By: {{ $notification->data['teacher'] }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Right side: relative time -->
                        <div class="notification-right">
                            <span class="pill">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>

</div>
@endsection