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
                <select name="sort" id="sort" class="search-select">
                    <option value="" disabled selected>Sort By</option>
                    <option value="alphabetical">Alphabetical</option>
                    <option value="date_created">Date Created</option>
                </select>
            <input class="search-input" type="text" id="searchInputAssessment" placeholder="Search generated assessments... ">
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
                                Archive
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
                aVal = new Date($(a).find('td:eq(3)').text());
                bVal = new Date($(b).find('td:eq(3)').text());
                return aVal - bVal;
        }
    });
    
    $tbody.empty().append($rows);
});
</script>
@endsection
