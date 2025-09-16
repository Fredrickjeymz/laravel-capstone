@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>My Assessments</h2>
        <p>Manage and review your assessments.</p>
    </div>
        <div class="table-container">
            <div class="assessment-type-card">
                <h3>Assigned Assessments</h3>
                <p>Assigned assessments are quizzespublished and officially linked to specific classes.</p>
                <div class="card-footer">
                    <span>Assigned Assessments</span>
                    <button class="view-assigned-btn" id="btn-assigned" data-url="{{ route('assigned-ass') }}">
                        View Assigned Assessments
                    </button>
                </div>
            </div>
            <h3>My Assessments</h3>
            <div class="search-bar">
                    <input class="search-input" type="text" id="searchInputSaved" placeholder="Search assessments">
                    <button class="search-btn" id="searchBtnSaved"><i class="fas fa-search"></i></button>
                </div>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Question Type</th>
                            <th>Subject</th>
                            <th>No. of Questions</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="saved-table">
                    @foreach ($savedAssessments as $assessment)
                        <tr>
                            <td>{{ $assessment->title ?? '-' }}</td>
                            <td>{{ $assessment->question_type }}</td>
                            <td>{{ $assessment->subject }}</td>
                            <td>{{ $assessment->questions->count() }}</td>
                            <td>{{ $assessment->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <button class="btn view-btn" data-id="{{ $assessment->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn saveddel" data-id="{{ $assessment->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                @endforeach
            </tbody>
        </table>
        <div class="information-card">
            <h3>Information</h3>
            <p>Holds data about all generated assessments, including titles, instructions, question types, creation dates, and their linkage to specific classes and students.</p>
        </div>
    </div>
</div>
<script>
    function fetchAssessments(page = 1, search = '') {
        const pageUrl = "{{ route('my-saved-assessments') }}?page=" + page + "&search=" + encodeURIComponent(search);

        $.ajax({
            url: pageUrl,
            type: 'GET',
            success: function (response) {
                const extracted = $(response).find('.saved-table').html();
                if (extracted) {
                    $('.saved-table').fadeOut(150, function () {
                        $(this).html(extracted).fadeIn(150);
                    });
                } else {
                    $('.saved-table').html('<p>No results found.</p>');
                }
            },
            error: function () {
                $('.saved-table').html('<p>Error loading assessments.</p>');
            }
        });
    }

    $(document).ready(function () {
        // Search button
        $(document).on('click', '#searchBtnSaved', function () {
            const search = $('#searchInputSaved').val();
            fetchAssessments(1, search);
        });
    });
</script>
@endsection
