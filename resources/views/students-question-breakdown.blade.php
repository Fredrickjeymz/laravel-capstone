@extends('MainLayout')

@section('content-area')
<div class="preview-area">
    <button class="btn-return" id="btn-return" data-url="{{ route('students.classes') }}">
        Return
    </button>
    <div class="top">
        <h2>Evaluation Results</h2>
        <p>Detailed scoring for {{ $score->student->fname }} {{ $score->student->mname }} {{ $score->student->lname }}'s submission</p>
    </div>
    
    <div class="generated-are-con">
        <div class="score-result-area-prev">

            <div class="sr-header">
                <h2>{{ $score->student->fname }} {{ $score->student->mname }} {{ $score->student->lname }}</h2>
                <h3>{{ $assessment->title }}</h3>
                <div class="div">
                    <div>
                        <p>Total Score</p>
                        <p class="tot">{{ $score->total_score }}/{{ $score->max_score }}</p>
                    </div>
                    <div>
                        <div class="percentage-div">
                            <p>Percentage </p>
                            <p>{{ round($score->percentage, 2) }}%</p>
                        </div>
                        <div class="bar"></div>
                    </div>
                </div>
            </div>

            <div class="question-breakdown">

                @php
                    $objectiveTypes = ['Multiple Choice', 'True Or False', 'Fill In The Blanks', 'Identification', 'Enumeration', 'Matching Type'];
                    $isObjective = in_array($assessment->question_type, $objectiveTypes);
                @endphp

                @foreach($score->questionScores as $qs)
                    @php
                    $question = $qs->question;
                    @endphp

                    @php
                        // Remove question number (e.g., "1.") if present
                        $cleaned_text = preg_replace('/^\d+[\.\)]\s*/', '', $question->question_text);

                        // Extract question (before A), B), etc.)
                        $question_text = preg_split('/\s*[A-D]\)[\s]*/', $cleaned_text)[0];

                        // Extract all choices (A) to D))
                        preg_match_all('/([A-D])\)\s*(.*?)(?=\s*[A-D]\)|$)/', $cleaned_text, $matches);
                    @endphp

                    <div class="question-card">

                        <div class="q-text">
                            <p>{{ trim($question_text) }}</p>
                            @foreach ($matches[1] as $key => $option_letter)
                            <p>{{ $option_letter }}) {{ trim($matches[2][$key]) }}</p>
                        @endforeach
                        </div>

                        <div class="ans-con">
                            <div class="student-answer">
                                <p>Student Answer:</p>
                                <p class="b">{{ $qs->student_answer }}</p>
                            </div>

                            @if($isObjective)
                                <div class="correct-answer">
                                    <p>Correct Answer:</p>
                                    <p class="b">{{ $qs->question->answer_key ?? 'N/A' }}</p>
                                </div>
                            @endif
                        </div>

                            @if($qs->criteria_scores)
                                @php
                                    $criteriaScores = is_string($qs->criteria_scores) ? json_decode($qs->criteria_scores, true) : $qs->criteria_scores;
                                @endphp

                                @if(is_array($criteriaScores))
                                    <div class="s-r-criteria">
                                        <div class="rubric-score">
                                            @foreach($criteriaScores as $item)
                                                <div>
                                                    <span>{{ $item['criteria'] }}:</span>
                                                    <span>{{ $item['score'] }} / {{ $item['max_score'] }} pts</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif

                        <div class="score-box">
                            <p>Score : {{ $qs->score_given }}</p>
                        </div>

                        @if($qs->feedback)
                            <div>
                                <p>Feedback:</p>
                                <p>{{ $qs->feedback }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="result-card">
            <div class="card-header">
                <div class="header-icon">
                <i class="fas fa-chart-line"></i>
                </div>
                <h2 class="card-title">Evaluation Result Summary</h2>
            </div>
            
            <div class="card-body">
                <div class="info-section">
                <div class="section-header">
                    <div class="section-icon">
                    <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>Student Information</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $score->student->fname }} {{ $score->student->mname }} {{ $score->student->lname }}</span>
                    </div>
                    <div class="info-item">
                    <span class="info-label">LRN:</span>
                    <span class="info-value">{{ $score->student->lrn }}</span>
                    </div>
                    <div class="info-item">
                    <span class="info-label">Class:</span>
                    <span class="info-value">{{ $score->student->class->class_name ?? 'N/A' }}</span>
                    </div>
                </div>
                </div>
                
                <div class="info-section">
                <div class="section-header">
                    <div class="section-icon">
                    <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Score Details</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                    <span class="info-label">Total Score:</span>
                    <span class="info-value">{{ $score->total_score }}/{{ $score->max_score }}</span>
                    </div>
                    <div class="info-item">
                    <span class="info-label">Percentage:</span>
                    <span class="info-value percentage">{{ round($score->percentage, 2) }}%</span>
                    </div>
                </div>
                </div>
                
                <div class="info-section">
                <div class="section-header">
                    <div class="section-icon">
                    <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Assessment Details</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                    <span class="info-label">Title: <span class="info-value">{{ $assessment->title }}</span></span>
                    </div>
                    <div class="info-item">
                    <span class="info-label">Subject:</span>
                    <span class="info-value">{{ $assessment->subject }}</span>
                    </div>
                </div>
                </div>
                
                <div class="action-section">
                <div class="section-header">
                    <div class="section-icon">
                    <i class="fas fa-download"></i>
                    </div>
                    <h3>Download Results</h3>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-pdf" id="dl-pdf-prev">
                    <i class="fas fa-file-pdf"></i>
                    PDF
                    </button>
                    <button class="btn btn-image" id="dl-img-prev">
                    <i class="fas fa-file-image"></i>
                    Image
                    </button>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('click', async function (e) {
    // PDF Button Handler
    if (e.target.closest('#dl-pdf-prev')) {
        const element = document.querySelector('.score-result-area-prev');

        const opt = {
            margin: [0, 0, 0, 0],
            filename: 'score-result.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: 'css' }
        };

        html2pdf().set(opt).from(element).save();
    }

    // Image Button Handler
    if (e.target.closest('#dl-img-prev')) {
        const element = document.querySelector('.score-result-area-prev');

        html2canvas(element, {
            scale: 2,
            useCORS: true
        }).then(canvas => {
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'score-result.png';
            link.click();
        });
    }
});
</script>
@endsection

