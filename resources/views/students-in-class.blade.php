@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <br>
    <button id="btn-return" class="btn-return" data-url="{{ route('classes') }}">
        Return
    </button>
    <div class="top">
        <h2>Students in {{ $class->class_name }}</h2>
        <p>Subject: {{ $class->subject }} | Year Level: {{ $class->year_level }}</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>The table lists all students enrolled in this class.</p>
        </div>
        <div id="student-table">
            <h3>Students</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Student's LRN</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Birthdate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>{{ $student->lrn }}</td>
                            <td>{{ $student->fname }} {{ $student->mname }} {{ $student->lname }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->gender }}</td>
                            <td>{{ $student->birthdate }}</td>
                            <td>
                               <button
                                class="btn btn-remove-student"
                                data-student-id="{{ $student->id }}"
                                data-class-id="{{ $class->id }}">
                                Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No students enrolled in this class.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
