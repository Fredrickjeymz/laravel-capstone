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
        <input class="search-input" type="text" id="InputArchivedAssessment" placeholder="Search">
        <button class="search-btn" id="BtnArchivedAssessment"><i class="fas fa-search"></i></button>
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
                        <td>{{ $assessments->created_at }}</td>
                        <td>
                        <button class="btn restore restore-btn" data-id="{{ $assessments->id }}"> <i class="fas fa-undo"></i> </button>
                        <button class="btn delete delete-btn" data-id="{{ $assessments->id }}"> <i class="fas fa-trash"></i> </button>
                        </td>                 

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    function fetchAssessments(page = 1, search = '') {
        const pageUrl = "{{ route('archivedassessment') }}?page=" + page + "&search=" + encodeURIComponent(search);

        $.ajax({
            url: pageUrl,
            type: 'GET',
            success: function (response) {
                const extracted = $(response).find('.archived-assessment-table').html();
                if (extracted) {
                    $('.archived-assessment-table').fadeOut(150, function () {
                        $(this).html(extracted).fadeIn(150);
                    });
                } else {
                    $('.archived-assessment-table').html('<p>No results found.</p>');
                }
            },
            error: function () {
                $('.archived-assessment-table').html('<p>Error loading assessments.</p>');
            }
        });
    }

    $(document).ready(function () {
        // Search button
        $(document).on('click', '#BtnArchivedAssessment', function () {
            const search = $('#InputArchivedAssessment').val();
            fetchAssessments(1, search);
        });
    });
</script>
@endsection
