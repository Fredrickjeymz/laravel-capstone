@extends('StudentMainLayout')

@section('content-area')
<div id="content-area">
    <br>
    <button  class="btn-return" id="btn-return-class" data-url="{{ route('student.classes') }}">
        Return
    </button>
    <div class="top">
        <h2>Quizzes for {{ $class->class_name }}</h2>
        <p>{{ $class->subject }}, {{ $class->teacher->lname }}, {{ $class->teacher->fname }} {{ $class->teacher->mname }}., {{ $class->teacher->position }}</p>
    </div>
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
                    <h3><i class="fas fa-file-alt"></i> {{ $assessment->title }}</h3>
                    <p><i class="fas fa-calendar-day"></i> Date: {{ $assessment->pivot->created_at->format('M d, Y') }}</p>
                </div>

                <p class="instructions">{{ $assessment->instructions }}</p>

                <div class="stats">
                    <p class="meta"><i class="fas fa-clock"></i> Time Limit: {{ $assessment->pivot->time_limit }} mins</p>
                    <p class="meta"><i class="fas fa-question-circle"></i> {{ $assessment->questions_count }} Questions</p>
                    <p class="meta"><i class="fas fa-toggle-on"></i> {{ $assessment->question_type }}</p>
                    <p><i class="fas fa-calendar-xmark"></i> Due: 
                        {{ $dueDate ? $dueDate->format('M d, Y h:i A') : 'N/A' }}
                    </p>
                    <p><i class="fas fa-pen-to-square"></i> {{ $alreadyTaken ? '1/1' : '0/1' }} Attempts</p>
                </div>

                <div class="quiz-actions">
                    @if ($alreadyTaken)
                        <div>
                            <button class="pends-completed completed" disabled>Completed</button>
                        </div>
                        <div class="score-rem">
                            <p><strong>Your score:</strong> {{ $studentScore->total_score }} / {{ $studentScore->max_score }}</p>
                            <p><strong>Remarks:</strong> {{ $studentScore->remarks ?? 'None' }}</p>
                        </div>
                    @elseif ($isDue)
                        <button class="pends-due due" disabled>Over Due</button>
                        <div class="score-rem">
                            <p class="overdue-msg">⏰ Your quiz deadline has passed. Please contact your teacher if you need to retake it.</p>
                        </div>
                    @else
                        <button class="view-btn take-quiz-btn" data-id="{{ $assessment->id }}">
                            <i class="fas fa-play-circle"></i> Take Quiz
                        </button>
                        <button class="pends" disabled>Pending</button>
                    @endif
                </div>
            </div>
        @empty   
            <p>No quizzes assigned yet for this class.</p>
        @endforelse
    </div>

</div>
@endsection