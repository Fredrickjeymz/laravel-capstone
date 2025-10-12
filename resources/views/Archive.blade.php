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
            <input class="search-input" type="text" id="InputArchivedEduc" placeholder="Search archived educators... ">
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
                            <td><!--<div class="inactive-stat">-->Inactive</td>
                        <td>
                        <!-- Restore Button -->
                        <button class="btn restore restore-teacher-btn" data-id="{{ $teacher->id }}">
                            <i class="fas fa-undo"></i> 
                        </button>

                        <!-- Delete Button 
                        <button class="btn delete delete-teacher-btn" data-id="{{ $teacher->id }}">
                            <i class="fas fa-trash"></i>
                        </button> -->
                    </td>       
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).on('input', '#InputArchivedEduc', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
@endsection