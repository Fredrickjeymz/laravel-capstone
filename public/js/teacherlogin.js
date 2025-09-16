$(document).ready(function () {
    $("#login-form").submit(function (e) {
        e.preventDefault();

        let formData = {
            _token: $("input[name='_token']").val(),
            username: $("#username").val(),
            password: $("#password").val(),
            remember: $("#remember").is(":checked") ? 1 : 0,
            role: $("#role").val() // ✅ Include selected role
        };

        $.ajax({
            url: "{{ route('teacherlogin') }}", // Adjust route if it's shared
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
                let errorText = xhr.responseJSON ? xhr.responseJSON.message : "Login failed!";
                $("#error-message").text(errorText).show();
            }
        });
    });
});
