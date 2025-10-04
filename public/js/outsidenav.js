console.log("✅ Navigation script loaded!");

$(document).on("click", "#btn-return-home, #btn-createaccount, #btn-adminlogin, #btn-teacherlogin", function (e) {
    e.preventDefault();
    let pageUrl = $(this).data("url");
    console.log("🟡 Clicked button. Will load:", pageUrl);

    $.ajax({
        url: pageUrl,
        type: "GET",
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#main-area").html();
            if (extracted) {
                $("#main-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #main-area found in response.");
                $("#main-area").html("<p>Error: Could not load content properly.</p>");
            }
        },
        error: function () {
            console.log("🔴 AJAX failed.");
            $("#main-area").html("<p>Error loading content.</p>");
        }
    });
});

$(document).ready(function () {
    // Open modal
    $(document).on('click', '#openForgotPasswordModal', function (e) {
        e.preventDefault();
        $('#forgotPasswordModal').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeForgotPasswordModal', function () {
        $('#forgotPasswordModal').fadeOut();
    });

    // Handle reset request
    $(document).on('click', '#resetPasswordBtn', function () {
        let email = $('#forgotEmail').val();
        let token = $('#csrf_token').val();

        if (!email) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Email',
                text: 'Please enter your email before requesting a reset.'
            });
            return;
        }

        $.ajax({
            url: '/forgot-password',
            type: 'POST',
            data: {
                _token: token,
                email: email
            },
            success: function (response) {
                $('#forgotPasswordModal').fadeOut();
                Swal.fire({
                    icon: 'success',
                    title: 'Email Sent!',
                    text: response.message || 'A password reset link has been sent to your email.'
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Request Failed',
                    text: xhr.responseJSON?.message || 'We could not send the reset link. Try again.'
                });
                console.log("🚨 AJAX ERROR:", xhr.responseText);
            }
        });
    });
});
