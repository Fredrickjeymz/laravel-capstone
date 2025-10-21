@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>Students</h2>
        <p>Manage Students</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>Stores detailed information about each registered student, including personal details and authentication credentials necessary for accessing the system.</p>
        </div>
        <h3>Students</h3>
        <button class="btn-add btn-add-stud-class"><i class="fas fa-plus"></i> New Student to a Class</button>
        <div class="search-bar">
            <div class="search-wrapper">
                <input class="search-input" type="text" id="searchInputAssessment" placeholder=" Search Students...">
            </div>
        </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Student's LRN</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Birthdate</th>
                    <th>Gender</th>
                    <th>Classes Enrolled</th>
                </tr>
            </thead>
            <tbody class="student-table">
            @foreach ($students as $student)
                <tr data-id="{{ $student->id }}">
                    <td>{{ $student->lrn }}</td>
                    <td>{{ $student->fname }} {{ $student->mname }} {{ $student->lname }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ \Carbon\Carbon::parse($student->birthdate)->format('F d, Y') }}</td>
                    <td>{{ ucfirst($student->gender) }}</td>
                    <td>
                        @if ($student->classes->isEmpty())
                            <span class="text-muted">No classes assigned</span>
                        @else
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach ($student->classes as $class)
                                    <li>
                                        {{ $class->class_name }} ({{ $class->year_level }})
                                        {{-- Optional remove link --}}
                                        {{-- <button data-student="{{ $student->id }}" data-class="{{ $class->id }}" class="btn-remove-student">Remove</button> --}}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>    
                </tr>
            @endforeach
            </tbody>
        </table>
       <div id="addModalClassStudent" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeAddModalClassStudent">&times;</span>
                <h2>Add New Student</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label>Select Student:</label>
                    <input placeholder="Type to search students..." list="studentsList" name="student_id" id="studentInput" required>

                    <datalist id="studentsList">
                        @foreach($allStudents as $student)
                            <option value="{{ $student->id }} {{ $student->fname }} {{ $student->mname }} {{ $student->lname }} (LRN: {{ $student->lrn }})"></option>
                        @endforeach
                    </datalist>
                    <small class="form-text">Start typing to see available students</small>
                </div>

                <div class="form-group">
                    <label>Select Class:</label>
                    <select name="school_class_id" required>
                        <option value="" disabled selected>Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }} - {{ $class->subject }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button id="closeAddModalClassStudent" class="btn btn-cancel">Cancel</button>
                    <button id="saveNewBtnClassStudent" class="btn btn-primary">Add Student</button>
                </div>

            </div>
        </div>

        <div id="editModal" class="custom-modal">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeModal">&times;</span>
                <h2>Edit Assessment Type</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                <input type="hidden" id="edit_id">

                <div class="form-group">
                    <label for="edit_typename">Type Name:</label>
                    <input type="text" id="edit_typename" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description:</label>
                    <textarea id="edit_description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_assessmenttype_id ">Assessment Type:</label>
                    <select id="edit_assessmenttype_id" required>
                         <option value="" disabled selected>Select question type</option>
                        <option value="1">Objective Assessment</option>
                        <option value="2">Subjective Assessment</option>
                    </select>
                </div>

                <button id="saveEditBtn-question" type="button" class="submit-btn">Update</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on('input', '#searchInputStudent', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>

@endsection