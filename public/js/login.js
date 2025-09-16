$(document).ready(function () {
    $('#login-form').on('submit', function (e) {
        e.preventDefault();

        const formData = {
            username: $('input[name="username"]').val(),
            password: $('input[name="password"]').val(),
            _token: $('input[name="_token"]').val()
        };

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                }
            },
            error: function (xhr) {
                let message = 'An error occurred.';

                if (xhr.status === 401 && xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: message
                });
            }
        });
    });
});
