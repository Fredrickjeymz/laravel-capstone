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
            <p>This table shows all the students' accounts, along with their information. Please take responsibility for managing them.</p>
        </div>
        <h3>Students</h3>
        <button class="btn-add btn-add-stud"><i class="fas fa-plus"></i> New Student</button>
        <div class="search-bar">
            <select name="sort" id="sort" class="search-select">
                <option value="" disabled selected>Sort By</option>
                <option value="alphabetical">Alphabetical</option>
                <option value="birthdate">Birthdate</option>
            </select>
            <input class="search-input" type="text" id="searchInputStudents" placeholder="Search students...">
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
                    <label>Sex:</label>
                    <select name="gender" required>
                        <option value="" disabled selected>Select Sex</option>
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
    $(document).on('input', '#searchInputStudents', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
<script>
    $(document).on('change', '#sort', function () {
        let sortBy = $(this).val();
        let rows = $('table.styled-table tbody tr').get();

        rows.sort(function (a, b) {
            let keyA, keyB, aVal, bVal;

            if (sortBy === 'alphabetical') {
                keyA = $(a).find('td:nth-child(2)').text().toLowerCase();
                keyB = $(b).find('td:nth-child(2)').text().toLowerCase();
            } else if (sortBy === 'birthdate') {
                aVal = new Date($(a).find('td:eq(4)').text());
                bVal = new Date($(b).find('td:eq(4)').text());
                return aVal - bVal;
            }

            if (keyA < keyB) return -1;
            if (keyA > keyB) return 1;
            return 0;
        });

        $.each(rows, function (index, row) {
            $('table.styled-table tbody').append(row);
        });
    });
</script>
@endsection