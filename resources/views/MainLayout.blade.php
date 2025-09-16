<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEPNAS FAG</title>
    <link rel="stylesheet" href="{{ asset('css/MainLayout.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
    lucide.createIcons();
    </script>
</head>
<body>
    
    <div class="w-container">
        <div class="header">
            <h2>SEPNAS Formative Assessment Generator</h2>
        </div>
        <div class="sub-container">
            <div class="nav">
                <div class="profile">
                    <center>
                    <div class="myimage">
                    <h1>{{ strtoupper(substr(auth('web')->user()->fname, 0, 1)) }}{{ strtoupper(substr(auth('web')->user()->lname, 0, 1)) }}</h1>
                    </div>
                    </center>
                    <p>{{ auth('web')->user()->fname }} {{ auth('web')->user()->mname }} {{ auth('web')->user()->lname }}
                        <button class="edit-profile-btn" id="btn-edit-profile"><i class="fas fa-edit"></i></button>
                    </p>
                </div>
                
                <center>
                    <div class="navigation">
                        <button id="btn-dashboard" data-url="{{ route('teacherdashboard') }}" class="active">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </button>
                        <button id="btn-generate" data-url="{{ route('generateassessment') }}" >
                            <i class="fas fa-file-alt"></i> Generate Assessment
                        </button>
                        <button id="btn-saved" data-url="{{ route('my-saved-assessments') }}">
                            <i class="fas fa-bookmark"></i> Assessments
                        </button>
                        <button id="btn-class" data-url="{{ route('classes') }}">
                            <i class="fas fa-school"></i> Classes
                        </button>
                        <button id="btn-student" data-url="{{ route('students.classes') }}">
                            <i class="fas fa-user-graduate"></i> Students
                        </button>
                        <br><br>

                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="button" id="logout-button">
                                <i class="fas fa-sign-out-alt"></i> Sign Out
                            </button>
                        </form>
                    </div>
                </center>
            </div>

            <div class="main-container" id="content-area">
                @section('content-area')

                @show
            </div>
        </div>
    </div>

</body>
<script>
    // File name display
    document.getElementById('answer_file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'No file chosen';
        document.getElementById('file-name').textContent = fileName;
    });

    // Form submission
    document.getElementById('evaluationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('evaluate-btn');
        const spinner = document.getElementById('evaluate-spinner');
        const text = document.getElementById('evaluate-text');
        
        btn.disabled = true;
        spinner.classList.remove('hidden');
        text.textContent = 'Evaluating...';
        
        const formData = new FormData(this);
        
        fetch('/evaluate-answers', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Evaluation failed. Please try again.');
        })
        .finally(() => {
            btn.disabled = false;
            spinner.classList.add('hidden');
            text.textContent = 'Evaluate Answers';
        });
    });
</script>
<script src="{{ asset('js/ajax-view.js') }}"></script>
<script src="{{ asset('js/generate-nav.js') }}"></script>
<script src="{{ asset('js/saveassessment.js') }}"></script>
<script src="{{ asset('js/navigation.js') }}"></script>
<script src="{{ asset('js/assessment.js') }}"></script>
<script src="{{ asset('js/generateobj.js') }}"></script>
<script src="{{ asset('js/download.js') }}"></script>
<script src="{{ asset('js/return-nav.js') }}"></script>
<script src="{{ asset('js/page-break.js') }}"></script>
<script src="{{ asset('js/download-word-img.js') }}"></script>
<script src="{{ asset('js/saved-download-word-image.js') }}"></script>
<script src="{{ asset('js/saved-download.js') }}"></script>
<script src="{{ asset('js/logout.js') }}"></script>
<script src="{{ asset('js/pagination.js') }}"></script>
<script src="{{ asset('js/scoring.js') }}"></script>
<script src="{{ asset('js/saved-scoring.js') }}"></script>
<script src="{{ asset('js/download-scores.js') }}"></script>
<script src="{{ asset('js/class-crud.js') }}"></script>
<script src="{{ asset('js/student-class-crud.js') }}"></script>
<script src="{{ asset('js/upload-assessment.js') }}"></script>
<script src="{{ asset('js/assigned-nav.js') }}"></script>
<script src="{{ asset('js/assigned-cud.js') }}"></script>
<script src="{{ asset('js/change-pass-teacher.js') }}"></script>
</html>
