@extends('OutsideMainLayout')

@section('main-area')
    <div class="big-txt">
        <h1>Intelligent Assessment Generation</h1>
        <p>Revolutionize your educational workflow with AI-powered, personalized assessment creation. <br> Focus on teaching, not paperwork.</p>
    </div>
    <div class="gs-area">
        <button class="get-started" id="btn-teacherlogin" data-url="{{ route('login') }}">
            Get Started 🚀
        </button>
    </div>
    <div class="boxes">
        <div class="first-box">
            <h1><i class="fas fa-brain"></i></h1>
            <h2>Smart Assessment Creation</h2>
            <p>Generate intelligent assessments aligned with curriculum standards and learning objectives, saving educators time while ensuring quality content.</p>
        </div>
        <div class="second-box">
            <h1><i class="fas fa-file-download"></i></h1>
            <h2>Downloadable Assessments</h2>
            <p>Easily download professionally formatted assessments in just one click ready to print, share, or integrate into your learning platform.</p>
        </div>
        <div class="third-box">
            <h1><i class="fas fa-hand-pointer"></i></h1>
            <h2>Easy to Use</h2>
            <p>Enjoy a clean, intuitive interface designed for teachers. Get started quickly with minimal training or technical knowledge required.</p>
        </div>
    </div>

@endsection

