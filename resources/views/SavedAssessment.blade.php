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
                    <select name="filter-quarter" id="filterQuarter">
                        <option value="" disabled selected>Filter by Quarter</option>s
                        <option value="First Quarter">First Quarter</option>
                        <option value="Second Quarter">Second Quarter</option>
                        <option value="Third Quarter">Third Quarter</option>
                        <option value="Fourth Quarter">Fourth Quarter</option>
                    </select>
                    <select name="subject" id="filterSubject" required>
                        <option value="" disabled selected>Filter by Subject</option>
                        <option value="Filipino">Filipino</option>
                        <option value="Science">Science</option>
                        <option value="English">English</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Araling Panlipunan">Araling Panlipunan</option>
                        <option value="Edukasyon sa Pagpapakatao">Edukasyon sa Pagpapakatao</option>
                        <option value="Physical Education">Physical Education</option>
                        <option value="Health">Health</option>
                        <option value="Music">Music</option>
                        <option value="Technology and Livelihood Education">Technology and Livelihood Education</option>
                    </select>
                    <input class="search-input" type="text" id="searchInputAssessment" placeholder="Search assessments...">
                </div>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Quarter</th>
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
                            <td>{{ $assessment->quarter }}</td>
                            <td>{{ $assessment->question_type }}</td>
                            <td>{{ $assessment->subject }}</td>
                            <td>{{ $assessment->questions->count() }}</td>
                            <td>{{ $assessment->created_at->format('F d, Y') }}</td>
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
    $(document).on('input', '#searchInputAssessment', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
<script>
    $(document).on('input', '#filterQuarter', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
<script>
    $(document).on('input', '#filterSubject', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
@endsection
