@extends('OutsideMainLayout')

@section('main-area')
    <div class="form-container">
        <div class="form-card">
            <h2 class="form-title">Welcome Back!</h2>
            <p class="form-subtitle">Sign in to Sepnas Assessment Generator</p>

            <form id="login-form">
                @csrf
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <small class="helper-text">Must be at least 8 characters, including numbers.</small>
                </div>

                <div class="forgot-password-link">
                    <a href="#" id="openForgotPasswordModal">Forgot Password?</a>
                </div>

                <button class="submit-btn-login" type="submit">Login</button>

                <div id="error-message" style="color: red; display: none; margin-top: 10px;"></div>
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
            e.preventDefault();

            let formData = {
                _token: $("input[name='_token']").val(),
                username: $("#username").val(),
                password: $("#password").val(),
            };

            $.ajax({
                url: "{{ route('login') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.redirect;
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


