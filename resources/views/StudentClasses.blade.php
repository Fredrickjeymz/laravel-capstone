@extends('StudentMainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>My Classes</h2>
        <p>Select a class to view available quizzes.</p>
    </div>
    <div class="class-cards-container">
       @foreach($classes as $class)
            <div class="class-card">
                <div class="class-card-header">
                    <div>
                        <h3><i class="fas fa-layer-group"></i> {{ $class->year_level }} {{ $class->class_name }} <span class="sub-name">- {{ $class->subject }}</span></h3>
                        <p class="description">
                            📖 Please access the class to see your available quizzes.
                        </p>
                        <div class="quiz-stats">
                            <span class="dot blue"></span> 
                            <strong>{{ $class->total_quizzes }}</strong> Total Quizzes

                            <span class="dot orange ml-4"></span> 
                            <strong>{{ $class->pending_quizzes }}</strong> Pending/Over Due
                        </div>
                    </div>
                    <div class="teacher-info">
                        <p><i class="fas fa-chalkboard-teacher"></i>{{ $class->teacher->lname }}, {{ $class->teacher->fname }} {{ $class->teacher->mname }}., {{ $class->teacher->position }}</p>
                            <button class="view-btn view-quizzes-btn" data-id="{{ $class->id }}">
                                <i class="fas fa-book-reader"></i> View Quizzes
                            </button>
                    </div>
                </div>
            </div>
         @endforeach
    </div>
</div>
@endsection
