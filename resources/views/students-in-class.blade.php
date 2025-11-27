@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <br>
    <button id="btn-return" class="btn-return" data-url="{{ route('classes') }}">
        Return
    </button>
    <div class="top">
        <h2>Students in {{ $class->class_name }}</h2>
        <p>Subject: {{ $class->subject }} | Year Level: {{ $class->year_level }}</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>The table lists all students enrolled in this class.</p>
        </div>
        <div id="student-table">
            <h3>Students in {{ $class->class_name }}</h3>
            <div class="table-header">
                <button class="btn-add btn-add-to-class" data-class-id="{{ $class->id }}">
                    <i class="fas fa-plus"></i> Add Student to This Class
                </button>
                
                <div class="search-bar">
                    <div class="search-wrapper">
                        <input class="search-input" type="text" id="searchInputAssessment" placeholder=" Search Students...">
                    </div>
                </div>
            </div>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Student's LRN</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Birthdate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="student-table">
                    @forelse($students as $student)
                        <tr class="student-row" data-id="{{ $student->id }}">
                            <td>
                                <button class="btn-expand" data-id="{{ $student->id }}" >+</button>
                            </td>
                            <td>{{ $student->lrn }}</td>
                            <td>{{ $student->fname }} {{ $student->mname }} {{ $student->lname }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ \Carbon\Carbon::parse($student->birthdate)->format('F d, Y') }}</td>
                            <td>{{ $student->gender }}</td>
                            <td>
                               <button
                                class="btn btn-remove-student"
                                data-student-id="{{ $student->id }}"
                                data-class-id="{{ $class->id }}">
                                Remove
                                </button>
                            </td>
                        </tr>
                        <tr class="assessment-row" id="assessments-{{ $student->id }}" style="display:none;">
                            <td colspan="7">
                                <div class="assessment-container">
                                </div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="addStudentToClassModal" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeAddStudentModal">&times;</span>
                <h2>Add Student to {{ $class->class_name }}</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                <input type="hidden" id="current_class_id" value="{{ $class->id }}"> <!-- Make sure this exists -->

                <div class="form-group">
                    <label for="studentSelect">Select Student:</label>
                    <input list="availableStudents" id="studentSelect" name="student_id" 
                        placeholder="Type to search students..." required>
                    <datalist id="availableStudents">
                        @foreach($allStudents as $student)
                            <option value="{{ $student->id }} | {{ $student->fname }} {{ $student->mname }} {{ $student->lname }} (LRN: {{ $student->lrn }})">
                        @endforeach
                    </datalist>
                </div>

                <div class="modal-actions">
                    <button type="button" id="cancelAddStudent" class="btn btn-cancel">Cancel</button>
                    <button type="button" id="saveStudentToClass" class="btn btn-primary">Add Student</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
