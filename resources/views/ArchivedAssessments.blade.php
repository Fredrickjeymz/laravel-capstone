@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
    <div class="top">
        <h2>Archived Assessments</h2>
        <p>Manage all assessments assessments across all educators.</p>
    </div>
    <div class="btn-group">
        <button id="btn-archived-teacher" data-url="{{ route('archivedteachers') }}">
            Educators
        </button>
        <button id="btn-archivedassessments" data-url="{{ route('archivedassessment') }}" class="active">
            Assessments
        </button>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>This table shows all archived assessments created by educators. Please take responsibility for managing them.</p>
        </div>
    <h3>All Assessments</h3>
    <div class="search-bar">
        <input class="search-input" type="text" id="InputArchivedAssessment" placeholder="Search archived assessments...">
    </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Creator</th>
                    <th>Question Type</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="archived-assessment-table">
                @foreach ($assessments as $assessments)
                    <tr>
                        <td>{{ $assessments->title }}</td>
                        <td>{{ $assessments->teacher->name ?? 'Archived' }}</td>
                        <td>{{ $assessments->question_type }}</td>
                        <td>{{ $assessments->created_at->format('F d, Y') }}</td>
                        <td>
                        <button class="btn restore restore-btn" data-id="{{ $assessments->id }}"> <i class="fas fa-undo"></i> </button>
                        <!--<button class="btn delete delete-btn" data-id="{{ $assessments->id }}"> <i class="fas fa-trash"></i> </button> -->
                        </td>                 

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).on('input', '#InputArchivedAssessment', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
@endsection
