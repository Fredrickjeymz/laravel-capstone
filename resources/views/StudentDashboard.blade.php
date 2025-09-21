@extends('StudentMainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>Student Dashboard</h2>
        <p>Welcome back, {{ auth('student')->user()->fname }} {{ auth('student')->user()->mname }} {{ auth('student')->user()->lname }}!</p>
    </div>
    <div class="dashboard-containers">
            <div class="stat-card">
                <div class="con-ti">
                    <h4>Completed Quizzes</h4>
                    <h4><i class="fas fa-check-circle"></i></h4>
                </div>
                <h1>{{ $completedQuizzes }}</h1>
                <p>Across all Classes</p>
            </div>
            <div class="stat-card">
                <div class="con-ti">
                    <h4>Average Score </h4>
                    <h4><i class="fas fa-chart-line"></i></h4>
                </div>
                <h1>{{ $averageScore }}%</h1>
                <p>Accross all completed quizzes</p>
            </div>
            <div class="stat-card">
                <div class="con-ti">
                    <h4>Pending/Over Due</h4>
                    <h4><i class="fas fa-hourglass-half"></i></h4>
                </div>
                <h1>{{ $pendingQuizzes }}</h1>
                <p>Across all Classes</p>
            </div>
            <div class="stat-card">
                <div class="con-ti">
                    <h4>My Classes </h4>
                    <h4><i class="fas fa-chalkboard-teacher"></i></h4>
                </div>
                <h1>{{ $classCount }}</h1>
                <p>Across the System</p>
            </div>
        </div>

        <div class="recent-shortcut">
            <div class="recent-activity">
                <h2>Pending Quizzes</h2>
                <p class="subtext">Complete these assessments.</p>

                @forelse($pendingQuizList as $quiz)
                    @php
                        $assignedClass = $quiz->assignedClasses->first(); // You can improve this later if there are multiple
                        $className = $assignedClass ? $assignedClass->class_name ?? 'Unnamed Class' : 'N/A';
                        $questionCount = $quiz->questions->count();
                    @endphp

                    <div class="activity-item">
                        <div class="details">
                            <div class="title">
                                {{ $quiz->title }} - {{ $quiz->subject }} ({{ $className }})
                            </div>
                            <div class="type">
                                {{ ucfirst($quiz->question_type) }} - {{ $questionCount }} item(s)
                                @if($assignedClass && $assignedClass->pivot->due_date)
                                    <span style="font-size: 0.9em; color: #888;">
                                        | Due: {{ \Carbon\Carbon::parse($assignedClass->pivot->due_date)->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="take-btn">
                            <button class="take-quiz-btn" data-id="{{ $quiz->id }}">
                                <i class="fas fa-play-circle"></i> Take Quiz
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="subtext">No pending quizzes. 🎉</p>
                @endforelse
            </div>

            <div class="shortcut">
                <h2>Quick Actions</h2>
                <p>Get started with these common tasks.</p>
                <div class="shortcut-buttons">
                <button id="btn-class-quick" data-url="{{ route('student.classes') }}"><span class="icon-circle"><i class="fas fa-chalkboard-teacher"></i></span>View Classes</button>
                <button id="btn-quiz-quick" data-url="{{ route('student.all-quizzes') }}"><span class="icon-circle"><i class="fas fa-eye"></i></span> View Quizzes</button>
                <button class="btn-edit-profile"><span class="icon-circle"><i class="fas fa-user-edit"></i></span> Edit Profile</button>
                <button class="btn-change-pass"><span class="icon-circle"><i class="fas fa-key"></i></span> Change Password</button>
                </div>
            </div>
        </div>
        <div id="ChangePassModal" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="CloseChangePassModal">&times;</span>
                <h2>Change Password</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label>Current Password:</label>
                    <input type="password" name="current_pass" required>
                </div>

                <div class="form-group">
                    <label>New Password:</label>
                    <input type="password" name="new_pass" required>
                </div>

                <div class="form-group">
                    <label>Confirm New Password:</label>
                    <input type="password" name="new_pass_confirmation" required>
                </div>

                <button id="studentsaveNewPass" class="submit-btn submit-btn-change">Save</button>
            </div>
        </div>
        @php
            $student = Auth::guard('student')->user();
        @endphp

        <div id="EditProfileModal" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="CloseEditProfileModal">&times;</span>
                <h2>Edit Profile</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="fname" value="{{ $student->fname }}" required>
                </div>

                <div class="form-group">
                    <label>Middle Name:</label>
                    <input type="text" name="mname" value="{{ $student->mname }}" required>
                </div>

                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="lname" value="{{ $student->lname }}" required>
                </div>

                <button id="saveNewProfile" class="submit-btn submit-btn-profile">Save</button>
            </div>
        </div>

</div>
@endsection
