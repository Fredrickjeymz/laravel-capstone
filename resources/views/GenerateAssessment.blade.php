@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="generation-area">
        <div class="btn-group">
            <a href=#><button class="active">Generate</button></a>
            <a href="/preview"><button>Preview</button></a>
        </div>
        <div class="top">
            <h2>Generate Assessment</h2>
            <p>Upload your learning material and configure your assessment details.</p>
        </div>
        <div class="generate-area">
            <div class="form-area">
            <div id="overlay-spinner" style="display:none;">
                <div class="spinner-container">
                    <div class="spinner"></div>
                    <p>⏳ Generating your assessment, Please wait.</p>
                </div>
            </div>
                <div class="form-area-txt">
                    <h3>Assessment Configuration</h3>
                    <p>Upload a file and set the parameters for your assessment.</p>
                </div>
                    <div class="ti-in">
                        <div class="ti">
                            <label for="title">Assessment Title</label>
                            <input type="text" name="title" placeholder=" e.g., Chapter 5 Quiz: Cell Structure ">
                        </div>
                        <div class="sub">
                            <label for="instruction">Subject</label>
                            <input type="text" name="subject" placeholder=" e.g., Science and Society">
                        </div>
                        <div class="in">
                            <label for="instruction">Assessment Instructions</label>
                            <input type="text" name="instruction" placeholder=" e.g., Answer all the following questions.">
                        </div> 
                    </div>
                    <label for="fileInput" class="file-label">Upload Learning Material (.Docx, .PDF, .PPT)</label>
                        <input type="file" class="file-input" id="fileInput" accept=".pdf,.docx,.pptx" required>
                    <div class="ti-in">
                        <div class="ti">
                            <label for="">Select Question Type</label>
                            <select name="question_type" class="dropdown-input" required>
                                <option value="" disabled selected>Select question type</option>
                                @foreach($questionTypes as $type)
                                    <option value="{{ $type->typename }}">{{ $type->typename }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sub">
                            <label for="">Number of Questions</label>
                            <input type="number" class="number-input" placeholder="e.g., 10" min="1" max="100" required>
                        </div>
                        <div class="in">
                            <label for="">Number of Options</label>
                            <input type="number" class="option-num" placeholder="e.g., 4" min="2" max="10">
                        </div>
                    </div>

                        <div class="blooms-config">
                            <p class="config-title">Bloom's Taxonomy Percentage Configuration</p>
                            <p>Total Allocated Percentage: <span id="total-bloom">0%</span></p>
                            <div class="blooms-inputs">
                                <div class="bloom-group" data-key="remember">
                                    <label>Remembering</label><br>
                                    <div class="bloom-slider">
                                        <input type="range" name="bloom[remember]" class="bloom-input" value="0" min="0" max="100" required>
                                        <output class="bloom-value">0%</output>
                                    </div>
                                </div>

                                <div class="bloom-group" data-key="understand">
                                    <label>Understanding</label><br>
                                    <div class="bloom-slider">
                                        <input type="range" name="bloom[understand]" class="bloom-input" value="0" min="0" max="100" required>
                                        <output class="bloom-value">0%</output>
                                    </div>
                                </div>

                                <div class="bloom-group" data-key="apply">
                                    <label>Applying</label><br>
                                    <div class="bloom-slider">
                                        <input type="range" name="bloom[apply]" class="bloom-input" value="0" min="0" max="100" required>
                                        <output class="bloom-value">0%</output>
                                    </div>
                                </div>

                                <div class="bloom-group" data-key="analyze">
                                    <label>Analyzing</label><br>
                                    <div class="bloom-slider">
                                        <input type="range" name="bloom[analyze]" class="bloom-input" value="0" min="0" max="100" required>
                                        <output class="bloom-value">0%</output>
                                    </div>
                                </div>

                                <div class="bloom-group" data-key="evaluate">
                                    <label>Evaluating</label><br>
                                    <div class="bloom-slider">
                                        <input type="range" name="bloom[evaluate]" class="bloom-input" value="0" min="0" max="100" required>
                                        <output class="bloom-value">0%</output>
                                    </div>
                                </div>

                                <div class="bloom-group" data-key="create">
                                    <label>Creating</label><br>
                                    <div class="bloom-slider">
                                        <input type="range" name="bloom[create]" class="bloom-input" value="0" min="0" max="100" required>
                                        <output class="bloom-value">0%</output>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <button id="generate-btn">Generate</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $(".option-num").prop("disabled", true);

        $(".dropdown-input").on("change", function () {
            const selectedType = $(this).val();

            if (selectedType === "Multiple Choice") {
                $(".option-num").prop("disabled", false);
            } else {
                $(".option-num").prop("disabled", true).val("");
            }
        });
    });
</script>
<script>
    function initializeBloomSliders() {
        const sliders = document.querySelectorAll('.bloom-input');
        const totalOutput = document.getElementById('total-bloom');

        function updateSliderLimits() {
            let total = 0;

            sliders.forEach(slider => {
                total += parseInt(slider.value) || 0;
            });

            // ✅ Only update totalOutput if it exists
            if (totalOutput) {
                totalOutput.textContent = total + "%";
            }

            sliders.forEach(slider => {
                const current = parseInt(slider.value) || 0;
                const remaining = 100 - (total - current);
                slider.max = remaining < 0 ? 0 : remaining;

                const output = slider.nextElementSibling;
                if (output) {
                    output.textContent = slider.value + "%";
                }
            });
        }

        sliders.forEach(slider => {
            const output = slider.nextElementSibling;
            if (output) {
                output.textContent = slider.value + "%";
            }

            slider.addEventListener('input', () => {
                if (output) {
                    output.textContent = slider.value + "%";
                }
                updateSliderLimits();
            });
        });

        updateSliderLimits();
    }

    // Initialize after DOM is ready
    document.addEventListener('DOMContentLoaded', initializeBloomSliders);
</script>

@endsection

   
