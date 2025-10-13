@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <br>
    <button id="btn-return" class="btn-return" data-url="{{ route('assigned-ass') }}">
        Return
    </button>
    <div class="top">
        <h2>Student's Score Results</h2>
        <p>Manage and review your students score result.</p>
    </div>
    <div class="table-container">
            <div class="information-card">
                <h3>Information</h3>
                <p>The table shows all the score result belongs to the specific assessment.</p>
            </div>
            <div id="assessment-table">
                <h3>Student's Scores for Assessment: {{ $assessment->title }} - <span>{{ $assessment->question_type }}</span></h3>
            <div class="search-bar">
                <input class="search-input" type="text" id="searchInputStudent" placeholder="Search students...">
            </div>
                    <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Total Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    @foreach($scores as $score)
                    <tr>
                        <td>{{ $score->student->fname }} {{ $score->student->mname }} {{ $score->student->lname }}</td>
                        <td>
                            @foreach($score->student->classes as $class)
                            <span class="badge">{{ $class->class_name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $score->total_score }}/{{ $score->max_score }}</td>
                        <td>
                            <button class="btn view-btn-res" data-id="{{ $score->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn saveddel-res" data-id="{{ $score->id }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    <script>
    $(document).on('input', '#searchInputStudent', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
</div>
@endsection
