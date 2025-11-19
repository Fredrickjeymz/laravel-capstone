<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEPNAS FAG</title>
    <link rel="stylesheet" href="{{ asset('css/AdminMainLayout.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    
    <div class="w-container">
        <div class="header">
            <img src="{{ asset('image/sepnas_logo.png') }}" class="school-logo" alt="school logo">
            <h2>SEPNAS Formative Assessment Generator</h2>
        </div>
        <div class="sub-container">
            <div class="nav">
                <center>
                <div class="admin-panel">
                    <h2>Admin Panel</h2>
                </div>
                </center>
                
                <center>
                <div class="navigation">
                    <!-- Dashboard Button -->
                    <button id="btn-dashboard" data-url="{{ route('admindashboard') }}" class="active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </button>

                    <!-- Teachers Button -->
                    <button id="btn-teachers" data-url="{{ route('teachers') }}">
                        <i class="fas fa-chalkboard-teacher"></i> Educators
                    </button>

                    <button id="btn-students" data-url="{{ route('students') }}">
                        <i class="fas fa-user-graduate"></i> Students
                    </button>

                    <!-- Generated Assessments Button -->
                    <button id="btn-generated" data-url="{{ route('generated') }}">
                        <i class="fas fa-save"></i> Generated Assessments
                    </button>

                    <!-- Question Types Button -->
                    <button id="btn-questions" data-url="{{ route('assessmenttype') }}">
                        <i class="fas fa-file-alt"></i> Assessment Types
                    </button>

                    <button id="btn-archive" data-url="{{ route('archivedteachers') }}">
                        <i class="fas fa-box-archive"></i> Archive
                    </button>
                    <button id="btn-logs" data-url="{{ route('admin.activity-log') }}">
                        <i class="fa-solid fa-history"></i> Activity Log
                    </button><br>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="button" id="logout-button">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </button>
                    </form>
                </div>
                </center>
            </div>

            <div class="main-container" id="admin-content-area">
                @section('admin-content-area')

                @show
            </div>
        </div>
    </div>
</body>

<script src="{{ asset('js/admin-navigation.js') }}"></script>
<script src="{{ asset('js/assessmenttypeupdate.js') }}"></script>
<script src="{{ asset('js/questiontype-nav.js') }}"></script>
<script src="{{ asset('js/admin-assessment-view.js') }}"></script>
<script src="{{ asset('js/admin-return.js') }}"></script>
<script src="{{ asset('js/archive.js') }}"></script>
<script src="{{ asset('js/logout.js') }}"></script>
<script src="{{ asset('js/question-type-crud.js') }}"></script>
<script src="{{ asset('js/data-analytics.js') }}"></script>
<script src="{{ asset('js/search.js') }}"></script>
<script src="{{ asset('js/student-crud.js') }}"></script>
<script src="{{ asset('js/teacher-add.js') }}"></script>
</html>
