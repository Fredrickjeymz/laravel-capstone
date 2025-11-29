@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
    <div class="top">
        <h2>Educators</h2>
        <p>Manage all Educators.</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>This table shows all the educators' accounts, along with their information. Please take responsibility for managing them.</p>
        </div>
        <h3>Educators</h3>
        <button class="btn-add btn-add-teacher"><i class="fas fa-plus"></i> New Educator</button>
        <div class="search-bar">
            <select name="sort" id="sort" class="search-select">
                <option value="" disabled selected>Sort By</option>
                <option value="alphabetical">Alphabetical</option>
                <option value="birthdate">Birthdate</option>
                <option value="title">Title</option>
            </select>
            <input class="search-input" type="text" id="searchInputEducator" placeholder="Search educator...">
        </div>
        <table id="teachers-table" class="styled-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Title</th>
                    <th>Contact No.</th>
                    <th>Gender</th>
                    <th>Birthdate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
                <tbody>
                    @foreach ($teachers as $teacher)
                        <tr data-id="{{ $teacher->id }}">
                            <td>{{ $teacher->fname }} {{ $teacher->mname }} {{ $teacher->lname }}</td>
                            <td>{{ $teacher->username }}</td>
                            <td>{{ $teacher->position }}</td>
                            <td>{{ $teacher->phone }}</td>
                            <td>{{ $teacher->gender }}</td>
                            <td>{{ \Carbon\Carbon::parse($teacher->birthdate)->format('F d, Y') }}</td>
                            <td><div class="active-stat">Active</div></td>
                            <td>
                                <button class="btn archive-teacher-btn" data-id="{{ $teacher->id }}">
                                    Archive
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
        </table>
    </div>
        <div id="addModalTeacher" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeAddModalTeacher">&times;</span>
                <h2>Add New Educator</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <label for="">First Name:</label>
                        <input type="text" name="fname" id="fname" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <label for="">Middle Name:</label>
                        <input type="text" name="mname" id="mname" placeholder="Middle Name" required>
                    </div>
                    <div class="form-group">
                        <label for="">Last Name:</label>
                        <input type="text" name="lname" id="lname" placeholder="Last Name" required>
                    </div>
                    <div class="form-group">
                        <label for="">Email:</label>
                        <input type="email" name="email" id="email" placeholder="Email" required>
                    </div>
               
                    <div class="form-group">
                        <label for="">Phone:</label>
                        <input type="text" name="phone" id="phone" placeholder="Phone" required>
                    </div>

                    <div class="form-group">
                        <label for="">Birthdate:</label>
                        <input type="date" name="birthdate" id="birthdate" placeholder="Birthdate" required>
                    </div>
            
                    <div class="form-group">
                        <label for="">Title/Position:</label>
                        <select name="position" id="position" required>
                            <option value="" disabled selected>Title</option>
                            <option value="Teacher I">Teacher I</option>
                            <option value="Teacher II">Teacher II</option>
                            <option value="Teacher III">Teacher III</option>
                            <option value="Teacher IV">Teacher IV</option>
                            <option value="Teacher V">Teacher V</option>
                            <option value="Teacher VI">Teacher VI</option>
                            <option value="Teacher VII">Teacher VII</option>
                            <option value="Master Teacher I">Master Teacher I</option>
                            <option value="Master Teacher II">Master Teacher II</option>
                            <option value="Master Teacher III">Master Teacher III</option>
                            <option value="Master Teacher IV">Master Teacher IV</option>
                            <option value="Master Teacher V">Master Teacher V</option>
                            <option value="Head Teacher I">Head Teacher I</option>
                            <option value="Head Teacher II">Head Teacher II</option>
                            <option value="Head Teacher III">Head Teacher III</option>
                            <option value="Head Teacher IV">Head Teacher IV</option>
                            <option value="Head Teacher V">Head Teacher V</option>
                            <option value="Head Teacher VI">Head Teacher VI</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Sex:</label>
                        <select name="gender" id="gender" required>
                            <option value="" disabled selected>Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
              
                <button id="saveNewBtnTeacher" class="submit-btn submit-btn-teacher">Add</button>
            </div>
        </div>
</div>
<script>
    $(document).on('input', '#searchInputEducator', function () {
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
        let rows = $('#teachers-table tbody tr').get();

        rows.sort(function (a, b) {
            let keyA, keyB, aVal, bVal;

            if (sortBy === 'alphabetical') {
                keyA = $(a).find('td:nth-child(1)').text().toLowerCase();
                keyB = $(b).find('td:nth-child(1)').text().toLowerCase();
            } else if (sortBy === 'birthdate') {
                aVal = new Date($(a).find('td:eq(5)').text());
                bVal = new Date($(b).find('td:eq(5)').text());
                return aVal - bVal;
            } else if (sortBy === 'title') {
                keyA = $(a).find('td:nth-child(3)').text().toLowerCase();
                keyB = $(b).find('td:nth-child(3)').text().toLowerCase();
            }

            if (keyA < keyB) return -1;
            if (keyA > keyB) return 1;
            return 0;
        });

        $.each(rows, function (index, row) {
            $('#teachers-table tbody').append(row);
        });
    });
</script>
@endsection
