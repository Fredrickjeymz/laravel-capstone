$(document).ready(function () {
    $('#logout-button').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: 'You will be logged out.',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: $('#logout-form').attr('action'),
                    type: 'POST',
                    data: $('#logout-form').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function (response) {
                            window.location.href = response.redirect;
                    },                    
                    error: function () {
                        Swal.fire('Oops!', 'Something went wrong during logout.', 'error');
                    }
                });
            }
        });
    });
});