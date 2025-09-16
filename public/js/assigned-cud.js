$(document).ready(function () {
    // Show edit modal with prefilled values
    $(document).on('click', '.btn-edit-time', function () {
        let id = $(this).data('id');
        let due = $(this).data('due');
        let limit = $(this).data('limit');

        $('#edit_id').val(id);
        $('#due_date').val(due);
        $('#time_limit').val(limit);

        $('#EditModalTime').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeEditModalTime', function () {
        $('#EditModalTime').fadeOut();
    });

    // Submit update
    $(document).on('click', '#editTime', function () {
        let id = $('#edit_id').val();
        let due = $('#due_date').val();
        let limit = $('#time_limit').val();

        $.ajax({
            url: `/assigned-assessments/${id}/update-time`,
            method: 'POST',
            data: {
                _token: $('#csrf_token').val(),
                due_date: due,
                time_limit: limit
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'The assignment was successfully updated.',
                    timer: 2000,
                    showConfirmButton: false
                });

                $('#EditModalTime').fadeOut();

                // Update the row in the table
                let row = $(`tr[data-id='${id}']`);
                row.find('.due-cell').text(response.due_date_formatted);     // formatted e.g. "July 05, 2025"
                row.find('.time-cell').text(response.time_limit_formatted); // formatted e.g. "30 mins"
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
});

$(document).on('click', '.delete-time-btn', function() {
    let id = $(this).data('id');
    let row = $(this).closest('tr'); // optional, for removing without reload
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    Swal.fire({
        title: 'Are you sure?',
        text: "This assessment will be permanently deleted.",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                 url: `/assigned-assessments/${id}/delete-time`,
                type: 'DELETE',
                data: {
                    _token: csrfToken
                },
                success: function (response) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The assessment has been removed.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    row.fadeOut(300, function () {
                        $(this).remove();
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'An error occurred.',
                        icon: 'error'
                    });
                }
            });
        }
    });
});