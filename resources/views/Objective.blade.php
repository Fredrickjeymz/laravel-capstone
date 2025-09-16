@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="generate-area">
        
        <div class="form-area">
            <div class="file-qn">
                <input type="file" class="file-input" accept=".pdf,.docx,.pptx" required>
                <input type="number" class="number-input" placeholder="Number of Questions" min="1" max="100" required>
            </div>
            <div class="qt-on">
                <select class="dropdown-input" required>
                    <option value="" disabled selected>Select Question Type</option>
                    <option value="TrueOrFalse">True or False</option>
                    <option value="multiplechoice">Multiple Choice</option>
                    <option value="FillInTheBlanks">Fill in the Blanks</option>
                </select>
                <input type="number" class="option-num" placeholder="Number of Options" min="2" max="10">
            </div>
            <div class="text-btn-generate">
                <p>Double-check your inputs before generating an objective assessment.</p>
                <button id="generate-btn">Generate</button>
            </div>
        </div>
    </div>
    <div class="generated-area">
  
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection



