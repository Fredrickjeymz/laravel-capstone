@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="preview-area">
        <div class="btn-group">
            <button id="btn-generate-nav" data-url="{{ route('generateassessment') }}">
                Generate
            </button>
            <a href=#><button class="active">Preview</button></a>
        </div>
        <div class="top">
            <h2>Assessment Preview</h2>
            <p>Review your generated assessment and download or save it.</p>
        </div>
        <div class="generated-are-con">
        <div class="generated-area">
            <div class="gen-del" data-id="{{ $assessment->id }}">
            <div class="mb-6">
                <center>
                <div class="q-t">
                    @if ($assessment->title)
                        <p class="text-sm text-gray-600">{{ $assessment->title }}<p>
                    @endif
                </div>
                <div class="q-s">
                    @if ($assessment->subject)
                        <p class="text-sm text-gray-600">{{ $assessment->subject }}<p>
                    @endif
                </div>
                </center>

                @if ($assessment->instructions)
                @php
                    $questionTypeLabels = [
                        'TrueOrFalse' => 'True or False',
                        'multiplechoice' => 'Multiple Choice',
                        'FillInTheBlanks' => 'Fill in the Blanks',
                        'Identification' => 'Identification',
                        'Enumeration' => 'Enumeration',
                        'Matchingtype' => 'Matching Type',
                        'Essay' => 'Essay',
                        'Short Answer Questions' => 'Short Answer Questions',
                        'Critically Thought-out Opinions' => 'Critically Thought-out Opinions',
                    ];
                @endphp
                    <p class="text-sm text-gray-600">
                        @if ($assessment->question_type)
                            <span class="q-i"> {{ $questionTypeLabels[$assessment->question_type] ?? $assessment->question_type }}: </span>
                        @endif
                    {{ $assessment->instructions }}</p>
                @endif
            </div>

            {{-- Questions --}}
            <div class="q-l">
                <ol class="question-list">
                @foreach ($assessment->questions as $index => $question)
                    <li>
                        @php
                            $cleaned_text = preg_replace('/^\d+[\.\)]\s*/', '', $question->question_text);
                            $question_text = preg_split('/\s*[A-Z]\)[\s]*/', $cleaned_text)[0];
                            preg_match_all('/\s*([A-Z])\)[\s]*(.*?)(?=\s*[A-Z]\)|$)/', $question->question_text, $matches);
                        @endphp
                        <p>{{ trim($question_text) }}</p>
                        <p>
                            @foreach ($matches[1] as $key => $option_letter)
                                <p>{{ $option_letter }}) {{ trim($matches[2][$key]) }}</p>
                            @endforeach
                        </p>
                    </li>
                @endforeach
                </ol>

                {{-- Matching Type Options --}}
                @if ($assessment->question_type === 'Matching Type')
                    @php
                        $matchingOptions = $assessment->questions->pluck('answer_key')->toArray();
                        shuffle($matchingOptions);
                    @endphp
                    <div class="mt-6">
                        <h4 class="font-semibold">Options</h4>
                            @foreach ($matchingOptions as $i => $option)
                                <p>{{ $option }}</p>
                            @endforeach
                    </div>
                @endif
            </div>

            {{-- Answer Key (for objective types only) --}}
            @if (!in_array($assessment->question_type, ['Essay', 'Short Answer Questions', 'Critically Thought-out Opinions']))
                <div class="mt-10 p-4 bg-green-50 border-l-4 border-green-400">
                    <p class="a-k">Answer Key</p>

                    @foreach ($assessment->questions as $index => $question)
                        <div class="mb-4">
                            @if ($assessment->question_type === 'Enumeration')
                                @php
                                    $items = preg_split('/\s*,\s*/', $question->answer_key);
                                @endphp
                                    <p>{{ $index + 1 }}. Answers:
                                    @foreach ($items as $item)
                                        <p><li><span class="cap">{{ $item }}</span></li></p>
                                    @endforeach
                                    </p>
                            @elseif ($assessment->question_type === 'Matching Type')
                                <p>
                                    {{ $index + 1 }}. {{ $question->answer_key }}
                                </p>
                            @else
                                <p class="text-green-800 ml-6"><span>{{ $index + 1 }}. </span><span class="cap">{{ $question->answer_key }}</span></p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Rubric (for subjective types only) --}}
            @if ($assessment->rubric)
                <div class="rubric-container">
                    <h3 class="rubric-title">Scoring Rubric</h3>

                    @php
                        $rows = preg_split("/\r\n|\n|\r/", trim($assessment->rubric));
                        $table = [];

                        foreach ($rows as $row) {
                            $cleaned = trim($row);
                            if ($cleaned === '' || preg_match('/^[-| ]+$/', $cleaned)) {
                                continue;
                            }
                            $table[] = array_map('trim', explode('|', $cleaned));
                        }
                    @endphp

                    @if (count($table) > 1)
                        <div class="rubric-table-wrapper">
                            <table class="rubric-table">
                                <thead>
                                    <tr>
                                        @foreach ($table[0] as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_slice($table, 1) as $row)
                                        <tr>
                                            @foreach ($row as $cell)
                                                <td>{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="rubric-text">{{ $assessment->rubric }}</p>
                    @endif
                </div>
            @endif
        </div>
        @if($assessment->status === 'pending' || $assessment->status === 'in-progress')
        <script>
        // Only refresh if we're actually on the preview page
        const isPreviewPage = window.location.pathname.includes('preview') || 
                            window.location.href.includes('assessment');

        if (isPreviewPage) {
            console.log('🔄 Auto-refresh enabled for preview page');
            
            setTimeout(function() {
                console.log('🔄 Refreshing preview page...');
                location.reload();
            }, 5000);
            
            // Show loading indicator
            document.addEventListener('DOMContentLoaded', function() {
                const questionList = document.querySelector('.question-list');
                if (questionList) {
                    questionList.innerHTML += '<li style="color: #666; font-style: italic;">🔄 Generating questions... (page will auto-refresh in 5 seconds)</li>';
                }
            });
        } else {
            console.log('❌ Not on preview page, skipping auto-refresh');
        }
        </script>
        @endif
        </div>
            <div class="generated-actions">
                <div class="actions-txt">
                    <h3>Assessment Actions</h3>
                    <p>Download or save your assessment.</p>
                    <h4>Download Format</h4>
                </div>
                <div class="download-btns">
                    <button class="pdf" id="download-pdf"><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="word" id="download-word"><i class="fas fa-file-word"></i> Word</button>
                    <button class="img" id="download-image"><i class="fas fa-image"></i> Image</button>
                    <div id="pdf-options-modal" class="custom-modal">
                        <div class="custom-modal-content">
                            <span class="close-btn" id="closePdfModal">&times;</span>
                            <h2>Export to PDF</h2>
                            <div class="form-group">
                                <label>Font Size:</label>
                                <input type="number" id="pdf-font-size" value="12">
                            </div>
                            <div class="form-group">
                                <label>Font Style:</label>
                                <select id="pdf-font-style">
                                    <option value="Arial">Arial</option>
                                    <option value="Helvetica">Helvetica</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Verdana">Verdana</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Paper Size:</label>
                                <select id="pdf-paper-size">
                                    <option value="a4">A4</option>
                                    <option value="letter">Letter</option>
                                    <option value="legal">Legal</option>
                                </select>
                            </div>
                            <button id="generate-pdf" class="submit-btn">Generate PDF</button>
                        </div>
                    </div>

                    <!-- ====== Image Export Modal ====== -->
                    <div id="image-options-modal" class="custom-modal">
                        <div class="custom-modal-content">
                            <span class="close-btn" id="closeImageModal">&times;</span>
                            <h2>Export to Image</h2>
                            <div class="form-group">
                                <label>Font Size:</label>
                                <input type="number" id="image-font-size" value="12">
                            </div>
                            <div class="form-group">
                                <label>Font Style:</label>
                                <select id="image-font-style">
                                    <option value="Arial">Arial</option>
                                    <option value="Helvetica">Helvetica</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Verdana">Verdana</option>
                                </select>
                            </div>
                            <button id="generate-image" class="submit-btn">Generate Image</button>
                        </div>
                    </div>

                    <!-- ====== Word Export Modal ====== -->
                    <div id="word-options-modal" class="custom-modal">
                        <div class="custom-modal-content">
                            <span class="close-btn" id="closeWordModal">&times;</span>
                            <h2>Export to Word</h2>
                            <div class="form-group">
                                <label>Font Size:</label>
                                <input type="number" id="word-font-size" value="12">
                            </div>
                            <div class="form-group">
                                <label>Font Style:</label>
                                <select id="word-font-style">
                                    <option value="Arial">Arial</option>
                                    <option value="Helvetica">Helvetica</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Verdana">Verdana</option>
                                </select>
                            </div>
                            <button id="generate-word" class="submit-btn">Generate Word</button>
                        </div>
                    </div>
                </div>
                <div class="save-del">
                <button 
                    class="eval btn-open-upload-modal"
                    data-assessment-id="{{ $assessment->id }}">
                    <i class="fas fa-upload"></i> Upload Assessment
                </button>
                <button class="del" data-id="{{ $assessment->id }}">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
                <div class="ass-details">
                    <h3>Assessment Details</h3>
                        <div class="det-cons-a">
                            <p>Title:</p>
                            <p class="v">{{ $assessment->title }}</p>
                        </div>
                        <div class="det-cons-a">
                            <p>Subject:</p>
                            <p class="v">{{ $assessment->subject }}</p>
                        </div>
                        <div class="det-cons-a">
                            <p>Question Type:</p>
                            <p class="v">{{ $assessment->question_type }}</p>
                        </div>
                        <div class="det-cons-a">
                            <p>Date Created:</p>
                            <p class="v">{{ $assessment->created_at }}</p>
                        </div>
                    </div>
                </div>
            </div>
                <div id="AssessmentUploadModal" class="custom-modal" style="display: none;">
                    <div class="custom-modal-content">
                        <span class="close-btn" id="closeAssessmentUploadModal">&times;</span>
                        <h2>Upload Assessment</h2>

                        <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="assessment_id" id="assessment_id"> {{-- dynamically filled on click --}}

                        <div class="form-group">
                            <label>Due Date:</label>
                            <input type="datetime-local" name="due_date" required>
                        </div>

                        <div class="form-group">
                            <label>Time (minutes):</label>
                            <input type="number" name="time_limit" required>
                        </div>

                        <div class="form-group">
                            <label>Select Class:</label>
                            <select name="school_class_id" required>
                                <option value="" disabled selected>Select Class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_name }} - {{ $class->subject }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button id="saveAssessmentUploadModal" class="submit-btn">Upload</button>
                </div>
        </div>
    </div>
</div>
@endsection
