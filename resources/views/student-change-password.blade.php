@extends('OutsideMainLayout')

@section('main-area')
<div class="form-container">
    <div class="form-card">
        <h2 class="form-title">Change Password</h2>
        <p class="form-subtitle">Enter your current password and choose a new one</p>

        <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_pass" placeholder="Enter your current password" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_pass" placeholder="Enter your new password" required>
            <small class="helper-text">Must contain at least 8 characters, including numbers.</small>
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="new_pass_confirmation" placeholder="Confirm your new password" required>
        </div>

        <button id="studentsaveNewPass" class="submit-btn submit-btn-change">Save</button>
    </div>
</div>
@endsection