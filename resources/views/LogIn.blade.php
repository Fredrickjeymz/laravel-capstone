@extends('OutsideMainLayout')

@section('main-area')
    <div class="form-container">
        <div class="form-card">
            <h2 class="form-title">Welcome Back!</h2>
            <p class="form-subtitle">Sign in to Sepnas Assessment Generator.</p>
            <form id="login-form">
            @csrf
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <small class="helper-text">Must contain at least 8 characters, including numbers.</small>
                </div>

                <div class="form-group">
                    <label>Login As</label>
                        <select class="role" name="role" id="role" required>
                            <option value="teacher"><i class="fas fa-chalkboard-teacher"></i> Teacher</option>
                            <option value="student"><i class="fas fa-user-graduate"></i> Student</option>
                            <option value="admin"><i class="fas fa-user-shield"></i> Admin</option>
                        </select>
                        <small class="helper-text">Choose your role.</small>
                </div>
                    <button class="submit-btn" type="submit">Login</button>

                    <div id="error-message" style="color: red; display: none; margin-top: 10px;"></div>
                    <div class="forgot-password-link">
                        <a href="#" id="openForgotPasswordModal">Forgot Password?</a>
                </div>
            </form>
        </div>
        <div id="forgotPasswordModal" class="custom-modal" style="display: none;">
            <div class="custom-modal-content">
                <span class="close-btn" id="closeForgotPasswordModal">&times;</span>
                <h2>Forgot Password</h2>

                <input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" id="forgotEmail" name="email" placeholder="Enter your email" required>
                </div>

                <button id="resetPasswordBtn" class="submit-btn">Send Reset Link</button>
            </div>
        </div>
    </div>
<script>
    $(document).ready(function () {
        $("#login-form").submit(function (e) {
            e.preventDefault(); // Prevent normal form submission

            let formData = {
                _token: $("input[name='_token']").val(),
                username: $("#username").val(),
                password: $("#password").val(),
                remember: $("#remember").is(":checked") ? 1 : 0,
                role: $("#role").val() // ✅ Add this to send the selected role
            };

            $.ajax({
                url: "{{ route('login') }}", 
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect; // Redirect on success
                    } else {
                        $("#error-message").text(response.message).show();
                    }
                },
                error: function (xhr) {
                    let errorText = xhr.responseJSON?.message || "Login failed!";
                    $("#error-message").text(errorText).show();
                }
            });
        });
    });
</script>
@endsection


