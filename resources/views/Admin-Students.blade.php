@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
    <div class="top">
        <h2>Students</h2>
        <p>Manage Students</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>Manage Students accross all classes.</p>
        </div>
        <h3>Students</h3>
        <button class="btn-add btn-add-stud"><i class="fas fa-plus"></i> New Student</button>
        <div class="search-bar">
            <input class="search-input" type="text" id="searchInputStudent" placeholder="Search">
            <button class="search-btn" id="searchBtnStudent"><i class="fas fa-search"></i></button>
        </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Student's LRN</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Birthdate</th>
                </tr>
            </thead>
            <tbody class="student-table">
            @foreach ($students as $stud)
                <tr data-id="{{ $stud->id }}">
                    <td class="lrn-cell">{{ $stud->lrn }}</td>
                    <td class="fullname-cell">{{ $stud->fname }} {{ $stud->mname }} {{ $stud->lname }}</td>
                    <td class="email-cell">{{ $stud->email }}</td>
                    <td class="gender-cell">{{ $stud->gender }}</td>
                    <td class="birthdate-cell">{{ \Carbon\Carbon::parse($stud->birthdate)->format('F d, Y') }}</td>    
                </tr>
            @endforeach
            </tbody>
        </table>
       <div id="addModalStudent" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeAddModalStudent">&times;</span>
                <h2>Add New Student</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label>Learning Reference Number:</label>
                    <input type="number" name="lrn" required placeholder="Learning Reference Number">
                </div>
                
                <div class="form-group">
                    <label>First Name:</label>
                    <input type="text" name="fname" required placeholder="First Name">
                </div>

                <div class="form-group">
                    <label>Middle Name:</label>
                    <input type="text" name="mname" placeholder="Middle Name/Initial">
                </div>

                <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="lname" placeholder="Last Name">
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required placeholder="Email">
                </div>

                <div class="form-group">
                    <label>Birthdate:</label>
                    <input type="date" name="birthdate" placeholder="Birthdate">
                </div>

                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <button id="saveNewBtnStudent" class="submit-btn submit-btn-stud">Add</button>
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
function fetchAssessments(page = 1, search = '') {
    const pageUrl = "{{ route('students') }}?page=" + page + "&search=" + encodeURIComponent(search);

    $.ajax({
        url: pageUrl,
        type: 'GET',
        success: function (response) {
            const extracted = $(response).find('.student-table').html();
            if (extracted) {
                $('.student-table').fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                $('.student-table').html('<tr><td colspan="6" class="text-center">No results found.</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error — status:', xhr.status, 'error:', error);
            console.log('responseText:', xhr.responseText);
            $('.student-table').html('<tr><td colspan="6" class="text-center">Error loading content — check console.</td></tr>');
        }
    });
}

    $(document).ready(function () {
        // Search button
        $(document).on('click', '#searchBtnStudent', function () {
            const search = $('#searchInputStudent').val();
            fetchAssessments(1, search);
        });
    });
</script>
@endsection