@extends('MainLayout')

@section('content-area')
<div class="top">
    <h2>Activity Log</h2>
    <p>Review your recent actions and track system activity.</p>
</div>
<div class="logs-container">

    @if($logs->isEmpty())
        <div class="empty-card">
            <p>No activity logs found.</p>
        </div>
    @else
        <div class="logs-scroll">
            <div class="logs-list">
                @foreach($logs as $log)
                    <article class="log-card">
                        <div class="log-left">
                            <div class="log-date">{{ $log->created_at->format('M d, Y') }}</div>
                            <div class="log-time">{{ $log->created_at->format('h:i A') }}</div>
                        </div>

                        <div class="log-main">
                            <div class="log-title">
                                <strong>{{ $log->action }}</strong>
                                <span class="log-meta"> • {{ $log->user_type ?? 'User' }}</span>
                            </div>

                            <div class="log-desc">{{ $log->description }}</div>

                            <div class="log-sub">
                                <span class="log-ip">IP: {{ $log->ip_address ?? 'N/A' }}</span>
                                <span class="log-device">Device: {{ \Illuminate\Support\Str::limit($log->user_agent ?? '-', 60) }}</span>
                            </div>
                        </div>

                        <div class="log-right">
                            <span class="pill">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection