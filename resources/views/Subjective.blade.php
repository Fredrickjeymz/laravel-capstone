@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="preview-area">
        <div class="generate-area">
        <button id="btn-return" data-url="{{ route('generateassessment') }}">
            Return
        </button>
        <div class="form-area">
            <div class="file-qn">
                <input type="file" class="file-input">
                <input type="number" class="number-input" placeholder="Number of Questions" min="1" max="100">
            </div>
            <div class="qt-on">
                <select class="dropdown-input">
                    <option value="" disabled selected>True or False</option>
                    <option value="truefalse">True or False</option>
                    <option value="multiplechoice">Multiple Choice</option>
                    <option value="fillintheblanks">Fill in the Blanks</option>
                </select>
            </div>
            <div class="text-btn-generate">
                <p>Double-check your inputs before generating a subjective assessment.</p>
                <button>Generate</button>
            </div>
        </div>
    </div>
    <div class="generated-area">
        <p style="height: 1000px; background: rgba(255,255,255,0.3);">Test Content</p>
    </div>
</div>
@endsection
