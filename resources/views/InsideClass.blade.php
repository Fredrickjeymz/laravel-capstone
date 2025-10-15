@extends('StudentMainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>Quizzes for {{ $class->class_name }}</h2>
        <p>{{ $class->subject }}, {{ $class->teacher->lname }}, {{ $class->teacher->fname }} {{ $class->teacher->mname }}., {{ $class->teacher->position }}</p>
    </div>
    <button  class="btn-return" id="btn-return-class" data-url="{{ route('student.classes') }}">
        <i class="fas fa-rotate-left"></i> Return
    </button>
    <div class="quiz-cards-container">
        @forelse ($class->assessments as $assessment)
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
                    <p class="meta"><i class="fas fa-clock"></i> Time Limit: {{ $assessment->pivot->time_limit }} mins - {{ $alreadyTaken ? '1/1' : '0/1' }} Attempts</p>
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
                        <div class="score-rem">
                            <p class="overdue-msg">⏰ Your quiz deadline has passed. Please contact your teacher if you need to retake it.</p>
                        </div>
                    @else
                        <button class="pends" disabled>Pending</button>
                        <button class="view-btn take-quiz-btn" data-id="{{ $assessment->id }}">
                            <i class="fas fa-play-circle"></i> Take Quiz
                        </button>
                    @endif
                </div>
            </div>
        @empty   
        <div class="empty-state">
            <p>No quizzes assigned yet for this class.</p>
        </div>
            
        @endforelse
    </div>

</div>
@endsection