@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
    <div class="top">
        <h2>Archive Educators</h2>
        <p>Manage archive educators.</p>
    </div>
    <div class="btn-group">
        <button  class="active">
            Educators
        </button>
        <button id="btn-archivedassessments" data-url="{{ route('archivedassessment') }}" >
            Assessments
        </button>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>This table shows all the archived educators' accounts, along with their information. Please take responsibility for managing them.</p>
        </div>
        <h3>Educators</h3>
        <div class="search-bar">
            <input class="search-input" type="text" id="InputArchivedTeacher" placeholder="Search ">
            <button class="search-btn" id="BtnArchivedTeacher"><i class="fas fa-search"></i></button>
        </div>
        <table class="styled-table">
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
            <tbody class="archived-teacher-table">
                @foreach ($teachers as $teacher)
                    <tr>
                            <td>{{ $teacher->fname }} {{ $teacher->mname }} {{ $teacher->lname }}</td>
                            <td>{{ $teacher->username }}</td>
                            <td>{{ $teacher->position }}</td>
                            <td>{{ $teacher->phone }}</td>
                            <td>{{ $teacher->gender }}</td>
                            <td>{{ \Carbon\Carbon::parse($teacher->birthdate)->format('F d, Y') }}</td>
                        <td><div class="inactive-stat">Inactive</div></td>
                        <td>
                        <!-- Restore Button -->
                        <button class="btn restore restore-teacher-btn" data-id="{{ $teacher->id }}">
                            <i class="fas fa-undo"></i> 
                        </button>

                        <!-- Delete Button -->
                        <button class="btn delete delete-teacher-btn" data-id="{{ $teacher->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>       
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    function fetchAssessments(page = 1, search = '') {
        const pageUrl = "{{ route('archivedteachers') }}?page=" + page + "&search=" + encodeURIComponent(search);

        $.ajax({
            url: pageUrl,
            type: 'GET',
            success: function (response) {
                const extracted = $(response).find('.archived-teacher-table').html();
                if (extracted) {
                    $('.archived-teacher-table').fadeOut(150, function () {
                        $(this).html(extracted).fadeIn(150);
                    });
                } else {
                    $('.archived-teacher-table').html('<p>No results found.</p>');
                }
            },
            error: function () {
                $('.archived-teacher-table').html('<p>Error loading assessments.</p>');
            }
        });
    }

    $(document).ready(function () {
        // Search button
        $(document).on('click', '#BtnArchivedTeacher', function () {
            const search = $('#InputArchivedTeacher').val();
            fetchAssessments(1, search);
        });
    });
</script>
@endsection