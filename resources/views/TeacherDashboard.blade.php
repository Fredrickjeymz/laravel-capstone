@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>Dashboard</h2>
        <p>Welcome to Sepnas Assessment Generator, Your formative assessment creation tool.</p>
    </div>
        <div class="dashboard-containers">
            <div class="stat-card">
                <div class="con-ti">
                    <h4>Generated Assessments</h4>
                    <h4><i class="fas fa-file-alt"></i></h4>
                </div>
                <h1>{{ $generatedAssessmentsCount }}</h1>
                <p>Created this month</p>
            </div>
            <div class="stat-card">
                <div class="con-ti">
                    <h4>Assigned Assessments </h4>
                    <h4><i class="fas fa-clipboard-list"></i></h4>
                </div>
                <h1>{{ $savedAssessmentsCount }}</h1>
                <p>Total assigned assessments</p>
            </div>
            <div class="stat-card">
                <div class="con-ti">
                    <h4>Classes </h4>
                    <h4><i class="fas fa-school"></i></h4>
                </div>
                <h1>{{ $classesCount }}</h1>
                <p>Across the system</p>
            </div>
            <div class="stat-card">
                <div class="con-ti">
                    <h4>Students </h4>
                    <h4><i class="fas fa-user-graduate"></i></h4>
                </div>
                <h1>{{ $studentsCount }}</h1>
                <p>Across all classes</p>
            </div>
        </div>
        <div class="recent-shortcut">
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <p class="subtext">Your recent assessment creation history</p>

                @foreach ($generatedAssessments->take(5) as $assessment)
                    <div class="activity-item">
                        <div class="icon-wrapper">
                            <svg class="doc-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8">
                                <path d="M9 12h6M9 16h6M8 4h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
                            </svg>
                        </div>
                        <div class="details">
                            <div class="title">{{ $assessment->title ?? 'Untitled Assessment' }}</div>
                            <div class="type">{{ ucfirst(str_replace('_', ' ', $assessment->question_type)) }}</div>
                        </div>
                        <div class="timestamp">{{ $assessment->created_at->diffForHumans() }}</div>
                    </div>
                @endforeach
            </div>

            <div class="shortcut">
                <h2>Quick Actions</h2>
                <p>Get started with these common tasks.</p>
                <div class="shortcut-buttons">
                <button id="btn-quick-generate" data-url="{{ route('generateassessment') }}"><span class="icon-circle"><i class="fas fa-plus"></i></span> Generate New Assessment</button>
                <button id="btn-quick-assigned" data-url="{{ route('assigned-ass') }}"><span class="icon-circle"><i class="fas fa-folder-open"></i></span> View Assigned Assessments</button>
                <button id="btn-edit-profile"><span class="icon-circle"><i class="fas fa-user-edit"></i></span> Edit Profile</button>
                <button id="btn-change-pass"><span class="icon-circle"><i class="fas fa-key"></i></span> Change Password</button>
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

                <button id="saveNewPass" class="submit-btn submit-btn-change">Save</button>
            </div>
        </div>
        @php
            $teacher = Auth::guard('web')->user();
        @endphp

        <div id="EditProfileModal" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="CloseEditProfileModal">&times;</span>
                <h2>Edit Profile</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="fname" value="{{ $teacher->fname }}" required>
                </div>

                <div class="form-group">
                    <label>Middle Name:</label>
                    <input type="text" name="mname" value="{{ $teacher->mname }}" required>
                </div>

                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="lname" value="{{ $teacher->lname }}" required>
                </div>

                <button id="saveNewProfile" class="submit-btn submit-btn-profile">Save</button>
            </div>
        </div>

    </div>
</div>
@endsection
