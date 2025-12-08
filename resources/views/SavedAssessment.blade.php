@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>My Assessments</h2>
        <p>Manage and review your assessments.</p>
    </div>
        <div class="table-container">
            <!--
            <div class="assessment-type-card">
                <h3>Assigned Assessments</h3>
                <p>Assigned assessments are quizzespublished and officially linked to specific classes.</p>
                <div class="card-footer">
                    <span>Assigned Assessments</span>
                    <button class="view-assigned-btn" id="btn-assigned" data-url="{{ route('assigned-ass') }}">
                        View Assigned Assessments
                    </button>
                </div>
            </div> -->
            <div class="information-card">
                <h3>Information</h3>
                <p>Holds data about all generated assessments, including titles, instructions, question types, creation dates, and their linkage to specific classes and students.</p>
            </div>
            <h3>My Assessments</h3>
            <div class="search-bar-assessments">
                <select name="filter-quarter" id="filterQuarter" class="search-select">
                    <option value="" disabled selected>Filter by Quarter</option>
                    <option value="All">Show All</option>
                    <option value="First Quarter">First Quarter</option>
                    <option value="Second Quarter">Second Quarter</option>
                    <option value="Third Quarter">Third Quarter</option>
                    <option value="Fourth Quarter">Fourth Quarter</option>
                </select>

                <select name="subject" id="filterSubject" class="search-select" required>
                    <option value="" disabled selected>Filter by Subject</option>
                    <option value="All">Show All</option>
                    <option value="Filipino">Filipino</option>
                    <option value="Science">Science</option>
                    <option value="English">English</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Araling Panlipunan">Araling Panlipunan</option>
                    <option value="Edukasyon sa Pagpapakatao">Edukasyon sa Pagpapakatao</option>
                    <option value="Physical Education">Physical Education</option>
                    <option value="Health">Health</option>
                    <option value="Music">Music</option>
                    <option value="Technology and Livelihood Education">
                        Technology and Livelihood Education
                    </option>
                </select>

                <select name="week" id="filterWeek" class="search-select" required>
                    <option value="" disabled selected>Filter by Week</option>
                    <option value="All">Show All</option>
                    <option value="Week 1">Week 1</option>
                    <option value="Week 2">Week 2</option>
                    <option value="Week 3">Week 3</option>
                    <option value="Week 4">Week 4</option>
                    <option value="Week 5">Week 5</option>
                    <option value="Week 6">Week 6</option>
                    <option value="Week 7">Week 7</option>
                    <option value="Week 8">Week 8</option>
                    <option value="Week 9">Week 9</option>
                    <option value="Week 10">Week 10</option>
                </select>

                <select name="sort" id="sort" class="search-select">
                    <option value="" disabled selected>Sort By</option>
                    <option value="alphabetical">Alphabetical</option>
                    <option value="date_created">Date Created</option>
                    <option value="number_of_questions">Number of Questions</option>   
                </select>

                <div class="search-wrapper">
                    <input class="search-input" type="text" id="searchInputAssessment" placeholder=" Search assessments...">
                </div>
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
    //add unfilter option
    $(document).on('input', '#filterQuarter', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
        if (searchText === 'all') {
            $('table.styled-table tbody tr').show();
        }
    });
</script>
<script>
    $(document).on('input', '#filterSubject', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
        if (searchText === 'all') {
            $('table.styled-table tbody tr').show();
        }
    });
</script>
<script>
    $(document).on('change', '#sort', function () {
        let sortBy = $(this).val();
        let $tbody = $('table.styled-table tbody');
        let $rows = $tbody.find('tr').get();
        
        $rows.sort(function(a, b) {
            let aVal, bVal;
            
            switch(sortBy) {
                case 'alphabetical':
                    aVal = $(a).find('td:eq(0)').text().toLowerCase();
                    bVal = $(b).find('td:eq(0)').text().toLowerCase();
                    return aVal.localeCompare(bVal);
                    
                case 'date_created':
                    aVal = new Date($(a).find('td:eq(5)').text());
                    bVal = new Date($(b).find('td:eq(5)').text());
                    return aVal - bVal;
                    
                case 'number_of_questions':
                    aVal = parseInt($(a).find('td:eq(4)').text()) || 0;
                    bVal = parseInt($(b).find('td:eq(4)').text()) || 0;
                    return aVal - bVal;
            }
        });
        
        $tbody.empty().append($rows);
    });
</script>
<script>
    $(document).on('input', '#filterWeek', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
        if (searchText === 'all') {
            $('table.styled-table tbody tr').show();
        }
    });
</script>
@endsection
