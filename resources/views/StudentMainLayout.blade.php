<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/StudentStyle.css') }}">
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

</head>
<body>
    <div class="w-container">
        <div class="header">
            <h2>Sepnas FAG</h2>
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <button type="button" id="logout-button">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </form>
        </div>
        <div class="sub-container">
            <div class="nav">
                <div class="profile">
                    <center>
                    <div class="myimage">
                    <h1>{{ strtoupper(substr(auth('student')->user()->fname, 0, 1)) }}{{ strtoupper(substr(auth('student')->user()->lname, 0, 1)) }}</h1>
                    </div>
                    </center>
                    <p>{{ auth('student')->user()->fname }} {{ auth('student')->user()->mname }} {{ auth('student')->user()->lname }}
                        <button class="edit-profile-btn btn-edit-profile"><i class="fas fa-edit"></i></button>
                    </p>
                </div>
                
                <center>
                    <div class="navigation">
                        <button id="btn-dashboard"
                            data-url="{{ route('stud-dash') }}"
                            class="nav-btn {{ request()->routeIs('stud-dash') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                        </button>

                        <button id="btn-class"
                            data-url="{{ route('student.classes') }}"
                            class="nav-btn {{ request()->routeIs('student.classes') || request()->is('student/quiz/*') ? 'active' : '' }}">
                            <i class="fas fa-chalkboard"></i> <span>Classes</span>
                        </button>

                        <button id="btn-quiz"
                            data-url="{{ route('student.all-quizzes') }}"
                            class="nav-btn {{ request()->routeIs('student.all-quizzes') ? 'active' : '' }}">
                            <i class="fas fa-question-circle"></i> <span>Assessments</span>
                        </button>  

                        <button id="btn-logs"
                            data-url="{{ route('student.activity-log') }}"
                            class="nav-btn {{ request()->routeIs('student.activity-log') ? 'active' : '' }}">
                            <i class="fa-solid fa-history"></i> <span>Logs</span>
                        </button>

                        <button id="btn-notif"
                            data-url="{{ route('student.notifications') }}"
                            class="nav-btn {{ request()->routeIs('student.notifications') ? 'active' : '' }}">
                            <i class="fa-solid fa-bell" aria-hidden="true"></i> <span>Notifications</span>
                        </button><br>
                    </div>
                </center>
            </div>

            <div class="main-container" id="student-content-area">
                @section('content-area')

                @show
            </div>
        </div>
    </div>
</body>
<script src="{{ asset('js/student-navigation.js') }}"></script>
<script src="{{ asset('js/logout.js') }}"></script>
<script src="{{ asset('js/view-quizzes.js') }}"></script>
<script src="{{ asset('js/stud-return-nav.js') }}"></script>
<script src="{{ asset('js/change-pass-student.js') }}"></script>
</html>