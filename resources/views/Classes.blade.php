@extends('MainLayout')

@section('content-area')
<div id="content-area">
    <div class="top">
        <h2>Classes</h2>
        <p>Manage Classes</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>Contains records of all class groups created within the platform, including class names, associated instructors, and scheduling details for organizing assessments.</p>
        </div>
        <h3>Classes</h3>
        <button class="btn-add btn-add-class"><i class="fas fa-plus"></i> New Class</button>
        <div class="search-bar">
            <select name="sort" id="sort" class="search-select">
                <option value="" disabled selected>Sort By</option>
                <option value="alphabetical">Alphabetical</option>
                <option value="number_of_students">Number of Students</option>   
                <option value="year_level">Year Level</option>
            </select>
            <div class="search-wrapper">
                <input class="search-input" type="text" id="searchInputAssessment" placeholder=" Search Classes...">
            </div>
        </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Subject</th>
                    <th>Year Level</th>
                    <th>Enrolled Students</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="question-table">
            @foreach ($classes as $class)
                <tr data-id="{{ $class->id }}">
                    <td class="classname-cell">{{ $class->class_name }}</td>
                    <td class="subject-cell">{{ $class->subject }}</td>
                    <td class="yearlevel-cell">{{ $class->year_level }}</td>
                    <td>{{ $class->students_count }}</td>
                    <td>
                    <button class="btn btn-edit-class"
                        data-id="{{ $class->id }}"
                        data-name="{{ $class->class_name }}"
                        data-subject="{{ $class->subject }}"
                        data-year="{{ $class->year_level }}">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn delete-class-btn" data-id="{{ $class->id }}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button class="btn btn-view-students"
                        data-url="{{ route('classes.viewStudents', $class->id) }}">
                        View Students
                    </button>
                    </td>       
                </tr>
            @endforeach
            </tbody>
        </table>
       <div id="addModalClass" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeAddModalClass">&times;</span>
                <h2>Add New Class</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label>Class Name:</label>
                    <input type="text" name="class_name" required>
                </div>

                <div class="form-group">
                    <label>Subject:</label>
                    <input type="text" name="subject">
                </div>

                <div class="form-group">
                    <label>Grade Level:</label>
                    <select name="year_level" required>
                        <option value="" disabled selected>Select Grade Level</option>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 9">Grade 9</option>
                        <option value="Grade 10">Grade 10</option>
                    </select>
                </div>

                <button id="saveNewBtnClass" class="submit-btn submit-btn-class">Add</button>
            </div>
        </div>

        <div id="EditModalClass" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeEditModalClass">&times;</span>
                <h2>Edit Class</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <input type="hidden" id="edit_class_id">

                <div class="form-group">
                    <label>Class Name:</label>
                    <input type="text" id="edit_class_name" required>
                </div>

                <div class="form-group">
                    <label>Subject:</label>
                    <input type="text" id="edit_subject">
                </div>

                <div class="form-group">
                    <label>Grade Level:</label>
                    <select id="edit_year_level" required>
                        <option value="" disabled>Select Grade Level</option>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 9">Grade 9</option>
                        <option value="Grade 10">Grade 10</option>
                    </select>
                </div>

                <button id="editNewBtnClass" class="submit-btn edit-btn-class">Update</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on('input', '#searchInputClass', function () {
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
                
            case 'number_of_students':
                aVal = parseInt($(a).find('td:eq(3)').text()) || 0;
                bVal = parseInt($(b).find('td:eq(3)').text()) || 0;
                return aVal - bVal;

            case 'year_level':
                aVal = $(a).find('td:eq(2)').text().toLowerCase();
                bVal = $(b).find('td:eq(2)').text().toLowerCase();
                return aVal.localeCompare(bVal);
        }
    });
    
    $tbody.empty().append($rows);
});
</script>
@endsection




