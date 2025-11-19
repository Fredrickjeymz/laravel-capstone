@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <!--
    <button id="btn-return" class="btn-return" data-url="{{ route('my-saved-assessments') }}">
        Return
    </button>-->
    <div class="top">
        <h2>Uploaded Assessments</h2>
        <p>Manage Assigned Assessments</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>Represents the assessments that have been officially assigned by educators to specific classes or students. This includes assignment metadata such as due dates, time limits, and tracking of student attempts and completion status.</p>
        </div>
        <h3>Uploaded Assessments</h3>
            <div class="search-bar">
                <select name="filter-quarter" id="filterQuarter" class="search-select">
                    <option value="" disabled selected>Filter by Quarter</option>
                    <option value="First Quarter">First Quarter</option>
                    <option value="Second Quarter">Second Quarter</option>
                    <option value="Third Quarter">Third Quarter</option>
                    <option value="Fourth Quarter">Fourth Quarter</option>
                </select>

                <div class="search-wrapper">
                    <input class="search-input" type="text" id="searchInputAssessment" placeholder=" Search assessments...">
                </div>
            </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Assigned</th>
                    <th>Due Date</th>
                    <th>Time</th>
                    <!-- <th>Date</th> -->
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($assignedAss as $assignment)
                    @php
                        $assessment = $assignment->assessment;
                        $class = $assignment->class;

                        // Skip rows with missing assessment or class
                        if (!$assessment || !$class) continue;

                        $totalStudents = $class->students->count();
                        $answeredCount = $assessment->studentScores
                            ->whereIn('student_id', $class->students->pluck('id'))
                            ->count();

                        $status = $assignment->due_date && $assignment->due_date->isPast() ? 'Completed' : 'Ongoing';

                        if ($answeredCount == $totalStudents && $totalStudents > 0) {
                            $status = 'Completed';
                        }
                    @endphp
                    <tr data-id="{{ $assignment->id }}">
                        <td>{{ $assessment->title }}</td>
                        <td>{{ $class->class_name }}</td> {{-- Assigned class --}}
                        <td class="due-cell">{{ $assignment->due_date ? $assignment->due_date->format('F d, Y') : 'N/A' }}</td>
                        <td class="time-cell">{{ $assignment->time_limit ? $assignment->time_limit . ' mins' : 'N/A' }}</td>
                        <!-- <td>{{ $assignment->created_at->format('F d, Y') }}</td> -->
                        <td>                               
                            @if($status == 'Ongoing')
                                {{ $status }}  
                            @elseif($status == 'Completed' || $answeredCount == $totalStudents)
                                <p><button class="btn btn-item-analysis" 
                                    data-assessment="{{ $assessment->id }}" 
                                    data-class="{{ $class->id }}">
                                    Completed - View Item Analysis
                                </button></p>
                               
                            @endif</td>
                        <td>{{ $answeredCount }}/{{ $totalStudents }}</td>
                            <td>
                                <button class="btn btn-edit-time" data-id="{{ $assignment->id }}" data-due="{{ $assignment->due_date }}" data-limit="{{ $assignment->time_limit }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn delete-time-btn" data-id="{{ $assignment->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <!-- <button class="btn btn-scores-view" data-id="{{ $assessment->id }}">
                                        View Scores
                                </button> -->
                            </td>
                        </tr>
                @endforeach
            </tbody>
        </table>
        <!-- ✅ Modal -->
        <div id="EditModalTime" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeEditModalTime">&times;</span>
                <h2>Edit Assigned Class Time</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                <input type="hidden" id="edit_id">

                <div class="form-group">
                    <label>Due Date:</label>
                    <input type="datetime-local" id="due_date" required>
                </div>

                <div class="form-group">
                    <label>Time Limit (in minutes):</label>
                    <input type="number" id="time_limit">
                </div>

                <button id="editTime" class="submit-btn edit-btn-time">Update</button>
            </div>
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
</div>

@endsection