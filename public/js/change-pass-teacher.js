$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Show modal
    $(document).on('click', '#btn-change-pass', function () {
        $('#ChangePassModal').fadeIn();
    });

    // Close modal
    $(document).on('click', '#CloseChangePassModal', function () {
        $('#ChangePassModal').fadeOut();
    });

    $(document).on('click', '#saveNewPass', function () {
        let formData = {
            _token: $('#csrf_token').val(),
            current_pass: $('input[name="current_pass"]').val(),
            new_pass: $('input[name="new_pass"]').val(),
            new_pass_confirmation: $('input[name="new_pass_confirmation"]').val()
        };

        $.ajax({
            url: '/change-pass',
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#ChangePassModal').fadeOut();
                Swal.fire({
                    icon: 'success',
                    title: 'Password changed!',
                    text: 'The password was successfully updated.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect; // ✅ redirect properly
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Change Failed',
                    text: xhr.responseJSON?.message || 'An error occurred while changing password.'
                });
                console.log("🚨 AJAX ERROR:", xhr.responseText);
            }
        });
    });
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Show modal
    $(document).on('click', '#btn-edit-profile', function () {
        $('#EditProfileModal').fadeIn();
    });

    // Close modal
    $(document).on('click', '#CloseEditProfileModal', function () {
        $('#EditProfileModal').fadeOut();
    });

    // Save profile changes
    $(document).on('click', '#saveNewProfile', function () {
        let formData = {
            _token: $('#csrf_token').val(),
            fname: $('input[name="fname"]').val(),
            mname: $('input[name="mname"]').val(),
            lname: $('input[name="lname"]').val(),
        };

        $.ajax({
            url: '/edit-profile-student',
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#EditProfileModal').fadeOut();
                Swal.fire({
                    icon: 'success',
                    title: 'Profile updated!',
                    text: 'Your profile was successfully updated.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect;
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: xhr.responseJSON?.message || 'An error occurred while updating your profile.'
                });
                console.log("🚨 AJAX ERROR:", xhr.responseText);
            }
        });
    });
});
