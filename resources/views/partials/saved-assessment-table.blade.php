<table class="styled-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Question Type</th>
            <th>Subject</th>
            <th>No. of Questions</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($savedAssessments as $assessment)
            <tr>
                <td>{{ $assessment->id }}</td>
                <td>{{ $assessment->title ?? '-' }}</td>
                <td>{{ $assessment->question_type }}</td>
                <td>{{ $assessment->subject }}</td>
                <td>{{ $assessment->questions->count() }}</td>
                <td>{{ $assessment->created_at->format('Y-m-d H:i') }}</td>
                <td>
                    <button class="btn view-btn" data-id="{{ $assessment->id }}"><i class="fas fa-eye"></i></button>
                    <button class="btn saveddel" data-id="{{ $assessment->id }}"><i class="fas fa-trash-alt"></i></button>
                    <button class="btn btn-scores-view" data-id="{{ $assessment->id }}">View Scores</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $savedAssessments->links() }}
