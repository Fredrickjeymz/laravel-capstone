@extends('AdminMainLayout')

@section('admin-content-area')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div id="admin-content-area">
    <div class="top">
        <h2>Assessment Types</h2>
        <p>Manage Assessment and Question Types</p>
    </div>
    <div class="table-container">
    <div class="assessment-type-card">
        <h3>Question Types</h3>
        <p>Includes multiple choice, true/false, and matching type questions, essays and etc.</p>
        <div class="card-footer">
            <span>9 Question Types</span>
            <button class="view-type-btn" id="btn-questiontypes" data-url="{{ route('questiontypes') }}">
                View Question Types
            </button>
        </div>
    </div>
        <h3>Assessment Types</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($assessmenttype as $type)
                <tr data-id="{{ $type->id }}">
                    <td >{{ $type->typename }}</td>
                    <td class="description-cell-ass">{{ $type->description }}</td>
                    <td>
                    <button class="btn edit-type-btn"  
                        data-id="{{ $type->id }}"
                        data-description="{{ $type->description }}">
                        <i class="fas fa-pen"></i>
                    </button>
                    </td>       
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="information-card">
            <h3>Information</h3>
            <p>Assessment types are categorized into Objective and Subjective types. Each category contains specific question types that educators can use when creating assessments. Click on "View Question Types" to manage the question types for each assessment category.</p>
        </div>
    </div>
    <div id="editModal" class="custom-modal">
        <div class="custom-modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <h2>Edit Assessment Type</h2>

            <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
            <input type="hidden" id="edit_id">

            <div class="form-group">
                <label for="edit_description">Description:</label>
                <textarea id="edit_description" required></textarea>
            </div>

            <button id="saveEditBtn" type="button" class="submit-btn">Update</button>
        </div>
    </div>
</div>
@endsection
