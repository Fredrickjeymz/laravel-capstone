@extends('StudentMainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>Assessments</h2>
        <p>Take your pending assessments and review those completed assessments.</p>
    </div>
        @foreach ($classes as $class)
    <h3 class="class-header">{{ $class->year_level }} {{ $class->class_name }} - {{ $class->subject }}</h3>

    <div class="quiz-cards-container">
        @php
            // Sort assessments: pending first (by due date), then completed, then overdue
            $sortedAssessments = $class->assessments->sortBy(function ($assessment) use ($student, $now) {
                $studentScore = $assessment->studentScores->firstWhere('student_id', $student->id);
                $alreadyTaken = $studentScore !== null;

                $dueDateRaw = $assessment->pivot->due_date ?? null;
                $dueDate = $dueDateRaw ? \Carbon\Carbon::parse($dueDateRaw) : null;
                $isDue = $dueDate && $now->gt($dueDate);

                // Sorting priority:
                // 0 = pending, 1 = completed, 2 = overdue
                if (!$alreadyTaken && !$isDue) {
                    $priority = 0; // pending
                } elseif ($alreadyTaken) {
                    $priority = 1; // completed
                } else {
                    $priority = 2; // overdue
                }

                return [$priority, $dueDate ?? now()->addYears(50)];
            });
        @endphp

        @forelse ($sortedAssessments as $assessment)
            @php
                $studentScore = $assessment->studentScores->firstWhere('student_id', $student->id);
                $alreadyTaken = $studentScore !== null;

                $dueDateRaw = $assessment->pivot->due_date ?? null;
                $dueDate = $dueDateRaw ? \Carbon\Carbon::parse($dueDateRaw) : null;
                $isDue = $dueDate && $now->gt($dueDate);
            @endphp

            <div class="quiz-card">
                <div class="quiz-header">
                    <h3><i class="fas fa-file-alt"></i> {{ $assessment->title }} - {{ $assessment->pivot->created_at->format('M d, Y') }}</h3>
                </div>
                <div class="stats">
                    <p class="meta"><i class="fas fa-clock"></i> Time Limit: {{ $assessment->pivot->time_limit }} mins</p>
                    <p class="meta"><i class="fas fa-toggle-on"></i> {{ $assessment->question_type }} - {{ $assessment->questions_count }} Questions</p>
                    <p><i class="fas fa-calendar-xmark"></i> Due: 
                        {{ $dueDate ? $dueDate->format('M d, Y h:i A') : 'N/A' }}
                    </p>
                </div>

                <div class="quiz-actions">
                    @if ($alreadyTaken)
                        <div>
                            <button class="pends-completed completed" disabled>Completed</button>
                        </div>
                        <div>
                            <button class="pends-completed completed" disabled><strong>Your score:</strong> {{ $studentScore->total_score }} / {{ $studentScore->max_score }}</button>
                        </div>
                        <div>
                             <button class="pends remarks" disabled><strong>Remarks:</strong> {{ $studentScore->remarks ?? 'None' }}</button>
                        </div>
                    @elseif ($isDue)
                        <button class="pends-due due" disabled>Over Due</button>
                            <button class="pends remarks" disabled>⏰ Your assessment deadline has passed. Please contact your teacher if you need to retake it.</button>
                    @else
                        <button class="pends" disabled>Pending</button>
                        <button class="view-btn take-quiz-btn" data-id="{{ $assessment->id }}">
                            <i class="fas fa-play-circle"></i> Take Assessment
                        </button>
                    @endif
                </div>
            </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No assessments available for this class yet.</p>
            <span class="empty-hint">Please check again later or ask your teacher for updates.</span>
        </div>
        @endforelse
    </div>
@endforeach


    </div>
</div>
@endsection
