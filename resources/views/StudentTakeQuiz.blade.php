@extends('StudentMainLayout')

@section('content-area')
<div id="content-area">
    <div class="quiz-question-card"  id="quiz-wrapper" data-time-limit="{{ $time_limit ?? 30 }}">
            <div class="quiz-question-header">
                <div>
                    <h2>{{ $assessment->title }} - {{ $assessment->subject }}</h2>
                    <p><strong>Instructions:</strong> {{ $assessment->question_type }} - {{ $assessment->instructions }}</p>
                </div>
                <div class="time-quiz">
                    <div>
                        <h3><i class="fas fa-clock"></i> Time: <span id="countdown-timer"></span></h3>
                    </div>
                <div>

                        <button type="button" class="view-btn view-quizzes-btn" data-id="{{ $class->id }}">
                            Exit Quiz
                        </button>
                    </div>
                </div>
            </div>
            <form id="quiz-form">
                @csrf
                <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

                @foreach ($assessment->questions as $index => $question)
                    <div class="quiz-form">
                        <div class="questions-card">

                            @php
                                // Remove question number (e.g., "1. ", "2) ")
                                $text = preg_replace('/^\s*\d+[\.\)]\s*/', '', $question->question_text);

                                // Remove everything from "A)..." onward for objective types
                                if (in_array($assessment->question_type, ['Multiple Choice', 'True Or False'])) {
                                    $text = preg_split('/\s*A\)\s*/', $text)[0];
                                    preg_match_all('/([A-D])\)\s*(.*?)(?=\s*[A-D]\)|$)/', $question->question_text, $matches);
                                }
                            @endphp

                            {{-- Question Text --}}
                            <p><strong>{{ $index + 1 }}.</strong> {{ trim($text) }}</p>

                            {{-- Choices --}}
                            @if ($assessment->question_type === 'True Or False')
                                @php
                                    $trueFalseOptions = ['True', 'False'];
                                @endphp
                                @foreach ($trueFalseOptions as $option)
                                    <div>
                                        <label>
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}">
                                            {{ $option }}
                                        </label>
                                    </div>
                                @endforeach
                            @elseif ($assessment->question_type === 'Multiple Choice')
                                @php
                                    preg_match_all('/([A-D])\)\s*(.*?)(?=\s*[A-D]\)|$)/', $question->question_text, $matches);
                                @endphp
                                @foreach ($matches[2] as $optIndex => $option)
                                    <div>
                                        <label>
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}">
                                            {{ chr(65 + $optIndex) }}) {{ $option }}
                                        </label>
                                    </div>
                                @endforeach

                            {{-- Subjective Questions --}}
                            @elseif (in_array($assessment->question_type, ['Essay', 'Short Answer Questions']))
                                <textarea name="answers[{{ $question->id }}]" rows="4" class="w-full border p-2" placeholder="Enter you answer"></textarea>

                            {{-- Fill-in or others --}}
                            @else
                                <input type="text" name="answers[{{ $question->id }}]" class="input-for-asnwers" placeholder="Enter you answer">
                            @endif

                        </div>
                    </div>
                @endforeach

                <div class="submit-area">
                    <center>
                        <button type="submit" class="btn-submit-quiz">	<i class="fas fa-paper-plane"></i> Submit Quiz</button>
                        <div class="warning">
                            <p><i class="fas fa-triangle-exclamation"></i> Once submitted, you cannot change your answers. Make sure you're ready!</p>
                        </div>
                     </center>
                </div>
                 <div id="overlay-spinner" style="display:none;">
                    <div class="spinner-container">
                        <div class="spinner"></div>
                             <p>⏳ Submitting you answers, Please wait.</p>
                        </div>
                </div>
            </form>

        </div>
</div>

<script>
let quizSubmitted = false;
let timerInterval; // 🔥 Declare it globally

document.addEventListener("DOMContentLoaded", function () {
    const wrapper = document.getElementById("quiz-wrapper");
    const timerDisplay = document.getElementById("countdown-timer");
    const quizForm = document.getElementById("quiz-form");

    const timeLimitMinutes = parseInt(wrapper.dataset.timeLimit) || 30;
    let remainingTime = timeLimitMinutes * 60;

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
        const secs = (seconds % 60).toString().padStart(2, '0');
        return `${mins}:${secs}`;
    }

    function countdown() {
        if (quizSubmitted) return; 

        timerDisplay.textContent = formatTime(remainingTime);

        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            Swal.fire({
                icon: 'info',
                title: '⏰ Time is up!',
                text: 'Your quiz will be submitted automatically.',
                showConfirmButton: false,
                timer: 2500
            }).then(() => {
                $(quizForm).trigger('submit');
            });
        }

        remainingTime--;
    }

    timerInterval = setInterval(countdown, 1000); // 👈 assign to global
    countdown(); 
});
</script>

<script>
let quizAlreadySubmitted = false;

$(document).on("submit", "#quiz-form", function () {
    quizAlreadySubmitted = true; // mark as submitted
});

$(document).on('click', '.view-quizzes-btn', function (e) {
    if (quizAlreadySubmitted) {
        return; // ✅ do nothing if already submitted
    }

    e.preventDefault();
    e.stopImmediatePropagation();

    const quizForm = document.getElementById("quiz-form");
    const classId = $(this).data("id");

    Swal.fire({
        title: "Exit Quiz?",
        text: "If you exit now, your answers will be submitted immediately, even if unfinished.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, submit & exit",
        cancelButtonText: "No, stay"
        }).then((result) => {
            if (result.isConfirmed) {
                $(quizForm).trigger('submit'); // ✅ only submit if YES
            }
            // else do nothing if Cancel
            });
        });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const wrapper = document.getElementById("quiz-wrapper");
        const quizForm = document.getElementById("quiz-form");
        const navButtons = document.querySelectorAll(".nav-btn");

        // 🔒 Disable nav buttons at quiz start
        navButtons.forEach(btn => {
            btn.disabled = true;
            btn.classList.add("disabled-nav");
        });

        // ✅ Re-enable when quiz is submitted
        $(quizForm).on("submit", function () {
            navButtons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove("disabled-nav");
            });
        });
    });
</script>

@endsection