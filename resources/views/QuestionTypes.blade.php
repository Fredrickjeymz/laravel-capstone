@extends('AdminMainLayout')

@section('admin-content-area')
<div id="admin-content-area">
    <button id="btn-return" class="btn-return" data-url="{{ route('assessmenttype') }}">
        Return
    </button>
    <div class="top">
        <h2>Question Types</h2>
        <p>Manage Question Types</p>
    </div>
    <div class="table-container">
        <div class="information-card">
            <h3>Information</h3>
            <p>Question types are subcategories of assessment types and can be either objective (e.g., multiple-choice) or subjective (e.g., essay).</p>
        </div>
        <h3>Question Types</h3>
        <button class="btn-add"><i class="fas fa-plus"></i> New Question Type</button>
        <div class="search-bar">
            <input class="search-input" type="text" id="searchInputQuestionTypes" placeholder="Search question types...">
        </div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Assessment Type</th>
                    <th>Question Type</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="question-table">
            @foreach ($questiontype as $type)
                <tr data-id="{{ $type->id }}">
                    <td class="assessmenttype-cell">{{ $type->assessmentType->typename ?? 'Unknown' }}</td>
                    <td class="typename-cell">{{ $type->typename }}</td>
                    <td class="description-cell">{{ $type->description }}</td>
                    <td>
                    <button class="btn edit-type-question"  
                    data-id="{{ $type->id }}"
                    data-typename="{{ $type->typename }}"
                    data-description="{{ $type->description }}"
                    data-assessmenttype_id="{{ $type->assessmenttype_id }}">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn delete-questiontype-btn" data-id="{{ $type->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                    </td>       
                </tr>
            @endforeach
            </tbody>
        </table>
        <div id="addModal" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeAddModal">&times;</span>
                <h2>Add New Question Type</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label for="new_typename">Type Name:</label>
                    <input type="text" id="new_typename" required>
                </div>

                <div class="form-group">
                    <label for="new_description">Description:</label>
                    <textarea id="new_description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="new_assessmenttype_id ">Assessment Type:</label>
                    <select id="new_assessmenttype_id" required>
                         <option value="" disabled selected>Select question type</option>
                        <option value="1">Objective Assessment</option>
                        <option value="2">Subjective Assessment</option>
                    </select>
                </div>

                <button id="saveNewBtn" class="submit-btn">Add</button>
            </div>
        </div>

        <div id="editModal" class="custom-modal">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeModal">&times;</span>
                <h2>Edit Assessment Type</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
                <input type="hidden" id="edit_id">

                <div class="form-group">
                    <label for="edit_typename">Type Name:</label>
                    <input type="text" id="edit_typename" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description:</label>
                    <textarea id="edit_description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_assessmenttype_id ">Assessment Type:</label>
                    <select id="edit_assessmenttype_id" required>
                         <option value="" disabled selected>Select question type</option>
                        <option value="1">Objective Assessment</option>
                        <option value="2">Subjective Assessment</option>
                    </select>
                </div>

                <button id="saveEditBtn-question" type="button" class="submit-btn">Update</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).on('input', '#searchInputQuestionTypes', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
</script>
@endsection