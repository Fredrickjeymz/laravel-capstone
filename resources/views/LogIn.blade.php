@extends('OutsideMainLayout')

@section('main-area')
    <div class="return-home">
    </div>
    <div class="login-form">
        <h1>Welcome Back</h1>
        <h3>Sign in to SEPNAS Assessment Generator</h3>
        <form id="login-form">
        @csrf
            <div class="input-group">
                <label for="username" class="label">Username</label>
                <center>
                <input type="text" id="username" name="username" placeholder="Username" required>
                </center>
            </div>
            <div class="input-group">
                <label for="password" class="label">Password</label>
                <center>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <center>
            </div>

            <div class="input-group">
                <label for="role" class="label">Login As</label>
                <center>
                    <select class="role" name="role" id="role" required>
                        <option value="teacher"><i class="fas fa-chalkboard-teacher"></i> Teacher</option>
                        <option value="student"><i class="fas fa-user-graduate"></i> Student</option>
                        <option value="admin"><i class="fas fa-user-shield"></i> Admin</option>
                    </select>
                </center>
            </div>
                <button type="submit">Login</button>

                <div id="error-message" style="color: red; display: none; margin-top: 10px;"></div>
        </form>
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


