$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {

    // Open modal and fill data
    $(document).on('click', '.edit-type-btn', function () {
        $('#edit_id').val($(this).data('id'));
        $('#edit_description').val($(this).data('description'));
        $('#editModal').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeModal', function () {
        $('#editModal').fadeOut();
    });
});

$(document).on('click', '#saveEditBtn', function () {
    let id = $('#edit_id').val();
    let description = $('#edit_description').val();

    console.log("Attempting to update:", { id, description });

    $.ajax({
        url: '/update-assessment-type', // Don't use `{{ route(...) }}` inside <script> if outside Blade
        method: 'POST',
        data: {
            id: id,
            description: description,
        },
        success: function (response) {
            $('#editModal').fadeOut();
            let row = $(`tr[data-id='${id}']`);
    
            row.find('.description-cell-ass').text(description);

            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'The question type was successfully updated.',
                timer: 2000,
                showConfirmButton: false
            });

            $.get(response.redirect, function () {
                let content = $('<div>').html().find('#admin-content-area').html();
                $("#admin-content-area").fadeOut(150, function () {
                    $(this).html(content).fadeIn(150);
                });
            });
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: xhr.responseJSON?.message || 'An error occurred while updating.'
            });
            console.log("🚨 AJAX ERROR:", xhr.responseText);
        }
    });
});

