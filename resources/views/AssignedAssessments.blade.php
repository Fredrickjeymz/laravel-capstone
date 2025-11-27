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
                    <option value="all">Show All</option>
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
                                <!--<button class="btn btn-scores-view" data-id="{{ $assessment->id }}">
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
                    <script>
                        (function () {
                            const modal = document.getElementById('EditModalTime');
                            const dueDateInput = modal.querySelector("input[id='due_date']");
                                let refreshInterval = null;

                                // Format Date -> "YYYY-MM-DDTHH:MM"
                                function toLocalDateTimeString(dt) {
                                    const year = dt.getFullYear();
                                    const month = String(dt.getMonth() + 1).padStart(2, '0');
                                    const day = String(dt.getDate()).padStart(2, '0');
                                    const hour = String(dt.getHours()).padStart(2, '0');
                                    const minute = String(dt.getMinutes()).padStart(2, '0');
                                    return `${year}-${month}-${day}T${hour}:${minute}`;
                                }

                                // Set min to current time rounded down to minute (or +0 minutes)
                                function updateMin() {
                                    const now = new Date();
                                    now.setSeconds(0, 0); // drop seconds & ms
                                    const minStr = toLocalDateTimeString(now);

                                    // Only update if changed to reduce reflows
                                    if (dueDateInput.min !== minStr) {
                                    dueDateInput.min = minStr;
                                    }

                                    // If user already selected a value that is now < min, replace it with min
                                    if (dueDateInput.value) {
                                    const selected = new Date(dueDateInput.value);
                                    if (selected < now) {
                                        // replace silently so it's not selectable
                                        dueDateInput.value = minStr;
                                    }
                                    }
                                }

                                // When modal is shown -> start updating min every 10s (keeps it always non-past)
                                function onModalShow() {
                                    updateMin();
                                    // update periodically while modal is open to keep min accurate
                                    if (!refreshInterval) refreshInterval = setInterval(updateMin, 10000);
                                }

                                // When modal is hidden -> stop updating
                                function onModalHide() {
                                    if (refreshInterval) {
                                    clearInterval(refreshInterval);
                                    refreshInterval = null;
                                    }
                                }

                                // Prevent manual/paste selection of past date/time
                                dueDateInput.addEventListener('input', function () {
                                    if (!this.value) return;
                                    const now = new Date();
                                    now.setSeconds(0, 0);
                                    const selected = new Date(this.value);
                                    if (selected < now) {
                                    // snap back to min if user types a past date/time
                                    this.value = this.min || toLocalDateTimeString(now);
                                    }
                                });

                                // Update min whenever user focuses/clicks the input (ensures up-to-date)
                                dueDateInput.addEventListener('focus', updateMin);
                                dueDateInput.addEventListener('click', updateMin);

                                // Use a MutationObserver to detect when modal display changes (hidden -> shown)
                                const mo = new MutationObserver(() => {
                                    // A modal "shown" in your code seems to be display:block; hidden is display:none
                                    const style = window.getComputedStyle(modal);
                                    if (style.display !== 'none') {
                                    onModalShow();
                                    } else {
                                    onModalHide();
                                    }
                                });

                                mo.observe(modal, { attributes: true, attributeFilter: ['style', 'class'] });

                                // As a fallback: if your modal is opened by toggling a class, also listen for clicks
                                // on any element that might open it (optional but harmless)
                                document.addEventListener('click', function () {
                                    // small delay so style/class toggles take effect before checking
                                    setTimeout(() => {
                                    const style = window.getComputedStyle(modal);
                                    if (style.display !== 'none') onModalShow();
                                    else onModalHide();
                                    }, 10);
                                });

                                // Clean up on page unload
                                window.addEventListener('beforeunload', () => {
                                    mo.disconnect();
                                    onModalHide();
                                });

                                // Initialize min at script load in case modal is already visible
                                updateMin();
                                })();
                            </script>   
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
        if (searchText === 'all') {
            $('table.styled-table tbody tr').show();
        }
    });
</script>
</div>

@endsection