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
                <div id="overlay-spinner" style="display: none;">
                    <div class="spinner-container">
                        <div class="spinner-ring"></div>
                        <p class="spinner-text">
                        <i class="fa-solid fa-brain"></i> Learning material extracted! Generating questions.<br>
                        <span>Please wait...</span>
                        </p>
                    </div>
                </div>
            
            <div class="generated-area" >
                <div class="gen-del" data-id="{{ $assessment->id }}">
                <div id="assessment-content" data-id="{{ $assessment->id }}" data-assessment-status="{{ $assessment->status }}">
                    <div class="mb-6">
                        <center>
                        <div class="q-t">
                            @if ($assessment->title)
                                <p class="text-sm text-gray-600 editable-field" data-field="title">{{ $assessment->title }}</p>

                            @endif
                        </div>
                        <div class="q-s">
                            @if ($assessment->subject)
                                <p class="text-sm text-gray-600 editable-field" data-field="subject">{{ $assessment->subject }}</p>
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
                                    <span class="q-i" > {{ $questionTypeLabels[$assessment->question_type] ?? $assessment->question_type }}: </span>
                                @endif
                            <span class="editable-field" data-field="instructions">{{ $assessment->instructions }}</span></p>
                        @endif
                    </div>

                    {{-- Questions --}}
                    <div id="question-area" class="q-l">
                        <ol class="question-list">
                        @foreach ($assessment->questions as $index => $question)
                            <li>
                                @php
                                    // Parse question text to extract question and options
                                    $raw_text = $question->question_text;
                                    
                                    // Extract question text (everything before first option letter)
                                    preg_match('/^(.*?)(?=\s+[A-Z]\))/s', $raw_text, $q_match);
                                    $question_text = trim($q_match[1] ?? $raw_text);
                                    
                                    // Extract all options (A), B), C), etc.)
                                    preg_match_all('/\s+([A-Z])\)\s+(.*?)(?=\s+[A-Z]\)|$)/s', $raw_text, $matches);
                                @endphp
                                <p class="editable-question" data-id="{{ $question->id }}">
                                    {{ $question_text }}
                                </p>
                                @if (!empty($matches[1]))
                                    <p>
                                        @foreach ($matches[1] as $key => $option_letter)
                                            <p class="editable-option" data-id="{{ $question->id }}" data-option="{{ $option_letter }}">{{ $option_letter }}) {{ trim($matches[2][$key]) }}</p>
                                        @endforeach
                                    </p>
                                @endif
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
                                        <p class="editable-option" data-option="{{ $option }}">{{ $option }}</p>
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
                                            // Handle both comma and semicolon separation
                                            $items = preg_split('/\s*[,;]\s*/', trim($question->answer_key));
                                            $items = array_filter($items); // Remove empty values
                                        @endphp
                                            <p>{{ $index + 1 }}. Answers:
                                            @foreach ($items as $item)
                                                <p><li><span class="cap editable-answer" data-id="{{ $question->id }}">{{ $item }}</span></li></p>
                                            @endforeach 
                                            </p>
                                    @elseif ($assessment->question_type === 'Matching Type')
                                        <p class="answer-key editable-answer" data-id="{{ $question->id }}">
                                            {{ $index + 1 }}. {{ $question->answer_key }}
                                        </p>
                                    @else
                                        <p class="text-green-800 ml-6"><span>{{ $index + 1 }}. </span><span class="cap editable-answer" data-id="{{ $question->id }}">{{ $question->answer_key }}</span></p>
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
                </div></div>
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
                    <button id="editAssessmentBtn" class="edit-btn">Edit Assessment</button>
                    <button id="finalizeAssessmentBtn" class="finalize-btn" style="display:none;">Finalize</button>

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
                            <input type="datetime-local" name="due_date" required step="60">
                            <script>
                                (function () {
                                const modal = document.getElementById('AssessmentUploadModal');
                                const dueDateInput = modal.querySelector("input[name='due_date']");
                                let refreshInterval = null;

                                // Format Date -> "YYYY-MM-DDTHH:MM"
                                function toLocalDateTimeString(dt) {
                                    const year = dt.getFullYear();
                                    const month = String(dt.getMonth() + 1).padStart(2, '0');
                                    const day = String(dt.getDate()).padStart(2, '0');
                                    const hour = String(dt.getHours()).padStart(2, '0');
                                    const minute = String(dt.getMinutes()).padStart(2, '0');
                                    return `${year}-${month}-${day}T${hour}:${minute}`;
                                }

                                // Set min to current time rounded down to minute (or +0 minutes)
                                function updateMin() {
                                    const now = new Date();
                                    now.setSeconds(0, 0); // drop seconds & ms
                                    const minStr = toLocalDateTimeString(now);

                                    // Only update if changed to reduce reflows
                                    if (dueDateInput.min !== minStr) {
                                    dueDateInput.min = minStr;
                                    }

                                    // If user already selected a value that is now < min, replace it with min
                                    if (dueDateInput.value) {
                                    const selected = new Date(dueDateInput.value);
                                    if (selected < now) {
                                        // replace silently so it's not selectable
                                        dueDateInput.value = minStr;
                                    }
                                    }
                                }

                                // When modal is shown -> start updating min every 10s (keeps it always non-past)
                                function onModalShow() {
                                    updateMin();
                                    // update periodically while modal is open to keep min accurate
                                    if (!refreshInterval) refreshInterval = setInterval(updateMin, 10000);
                                }

                                // When modal is hidden -> stop updating
                                function onModalHide() {
                                    if (refreshInterval) {
                                    clearInterval(refreshInterval);
                                    refreshInterval = null;
                                    }
                                }

                                // Prevent manual/paste selection of past date/time
                                dueDateInput.addEventListener('input', function () {
                                    if (!this.value) return;
                                    const now = new Date();
                                    now.setSeconds(0, 0);
                                    const selected = new Date(this.value);
                                    if (selected < now) {
                                    // snap back to min if user types a past date/time
                                    this.value = this.min || toLocalDateTimeString(now);
                                    }
                                });

                                // Update min whenever user focuses/clicks the input (ensures up-to-date)
                                dueDateInput.addEventListener('focus', updateMin);
                                dueDateInput.addEventListener('click', updateMin);

                                // Use a MutationObserver to detect when modal display changes (hidden -> shown)
                                const mo = new MutationObserver(() => {
                                    // A modal "shown" in your code seems to be display:block; hidden is display:none
                                    const style = window.getComputedStyle(modal);
                                    if (style.display !== 'none') {
                                    onModalShow();
                                    } else {
                                    onModalHide();
                                    }
                                });

                                mo.observe(modal, { attributes: true, attributeFilter: ['style', 'class'] });

                                // As a fallback: if your modal is opened by toggling a class, also listen for clicks
                                // on any element that might open it (optional but harmless)
                                document.addEventListener('click', function () {
                                    // small delay so style/class toggles take effect before checking
                                    setTimeout(() => {
                                    const style = window.getComputedStyle(modal);
                                    if (style.display !== 'none') onModalShow();
                                    else onModalHide();
                                    }, 10);
                                });

                                // Clean up on page unload
                                window.addEventListener('beforeunload', () => {
                                    mo.disconnect();
                                    onModalHide();
                                });

                                // Initialize min at script load in case modal is already visible
                                updateMin();
                                })();
                            </script>   
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
            <script>
            document.getElementById("editAssessmentBtn").addEventListener("click", function () {

                document.getElementById("finalizeAssessmentBtn").style.display = "inline-block";
                this.style.display = "none";

                // Editable fields
                document.querySelectorAll(".editable-field").forEach(el => {
                    const value = el.innerText.trim();
                    el.innerHTML = `<input type="text" class="edit-input" data-field="${el.dataset.field}" value="${value}">`;
                });

                // Editable questions
                document.querySelectorAll(".editable-question").forEach(el => {
                    const value = el.innerText.trim();
                    el.innerHTML = `<textarea class="edit-textarea" data-id="${el.dataset.id}">${value}</textarea>`;
                });

                // Editable answer keys
                document.querySelectorAll(".editable-answer").forEach(el => {
                    const value = el.innerText.trim();
                    el.innerHTML = `
                        <input type="text" 
                            class="edit-input edit-answer" 
                            data-type="direct"
                            data-id="${el.dataset.id}" 
                            value="${value}">
                    `;
                });

                // Editable MCQ options - preserve letter
                document.querySelectorAll(".editable-option").forEach(el => {
                    const fullText = el.innerText.trim();
                    // Extract just the option text (remove "A) ", "B) ", etc.)
                    const optionText = fullText.replace(/^[A-Z]\)\s*/, '').trim();
                    const optionLetter = el.dataset.option;
                    
                    el.innerHTML = `
                        <input type="text" 
                            class="edit-input edit-option"
                            data-type="option"
                            data-id="${el.dataset.id}"
                            data-option="${optionLetter}"
                            value="${optionText}"
                            placeholder="${optionLetter}) ">
                    `;
                });

            });


            document.getElementById("finalizeAssessmentBtn").addEventListener("click", function () {

                const assessmentId = document.getElementById("assessment-content").dataset.id;

                let payload = {
                    _token: document.getElementById("csrf_token").value,
                    assessment_id: assessmentId,
                    fields: {},
                    questions: [],
                    answers: []
                };

                // FIELDS
                document.querySelectorAll(".edit-input[data-field]").forEach(el => {
                    payload.fields[el.dataset.field] = el.value.trim();
                });

                // QUESTIONS
                document.querySelectorAll(".edit-textarea").forEach(el => {
                    payload.questions.push({
                        id: el.dataset.id,
                        question_text: el.value.trim()
                    });
                });

                // MCQ OPTIONS - preserve original structure
                document.querySelectorAll(".edit-option").forEach(el => {
                    payload.answers.push({
                        type: "option",
                        question_id: el.dataset.id,
                        option_label: el.dataset.option,
                        option_text: el.value.trim()
                    });
                });

                // DIRECT ANSWER KEYS
                document.querySelectorAll(".edit-answer").forEach(el => {
                    payload.answers.push({
                        type: "direct",
                        question_id: el.dataset.id,
                        answer_key: el.value.trim()
                    });
                });

                console.log("Sending payload:", payload); // Debug

                fetch("/assessment/update", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.getElementById("csrf_token").value
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(res => {
                    console.log("Response:", res); // Debug
                    if (res.success) {
                        alert("Assessment updated successfully!");
                        location.reload();
                    } else {
                        alert("Failed to update assessment: " + (res.message || "Unknown error"));
                    }
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    alert("Error updating assessment: " + err.message);
                });
            });
            </script>
        </div>
    </div>
</div>
@endsection
