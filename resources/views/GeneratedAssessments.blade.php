@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
    <div class="top">
        <h2>Generated Assessments</h2>
        <p>Manage all generated assessments across all educators.</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>This table shows all assessments created by educators. Please take responsibility for managing them.</p>
        </div>
        <h3>All Generated Assessments</h3>
        <div class="search-bar">
            <input class="search-input" type="text" id="searchInputGenerated" placeholder="Search ">
            <button class="search-btn" id="searchBtnGenerated"><i class="fas fa-search"></i></button>
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
            <tbody class="assessment-table">
                @foreach ($assessments as $assessments)
                    <tr>
                        <td>{{ $assessments->title }}</td>
                        <td>{{ $assessments->teacher->fname ?? 'Unknown' }} {{ $assessments->teacher->mname ?? 'Unknown' }} {{ $assessments->teacher->lname ?? 'Unknown' }}</td>
                        <td>{{ $assessments->question_type }}</td>
                        <td>{{ $assessments->created_at->format('F d, Y') }}</td>
                        <td>
                            <button class="btn view-btn" data-id="{{ $assessments->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn archive-btn" data-id="{{ $assessments->id }}">
                                <i class="fas fa-box-archive"></i>
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
        const pageUrl = "{{ route('generated') }}?page=" + page + "&search=" + encodeURIComponent(search);

        $.ajax({
            url: pageUrl,
            type: 'GET',
            success: function (response) {
                const extracted = $(response).find('.assessment-table').html();
                if (extracted) {
                    $('.assessment-table').fadeOut(150, function () {
                        $(this).html(extracted).fadeIn(150);
                    });
                } else {
                    $('.assessment-table').html('<p>No results found.</p>');
                }
            },
            error: function () {
                $('.assessment-table').html('<p>Error loading assessments.</p>');
            }
        });
    }

    $(document).ready(function () {
        // Search button
        $(document).on('click', '#searchBtnGenerated', function () {
            const search = $('#searchInputGenerated').val();
            fetchAssessments(1, search);
        });
    });
</script>
@endsection
