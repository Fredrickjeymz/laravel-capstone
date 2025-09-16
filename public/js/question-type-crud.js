$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {

    // Open modal and fill data
    $(document).on('click', '.edit-type-question', function () {
        $('#edit_id').val($(this).data('id'));
        $('#edit_typename').val($(this).data('typename'));
        $('#edit_description').val($(this).data('description'));
        $('#edit_assessmenttype_id').val($(this).data('assessmenttype_id'));
        $('#editModal').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeModal', function () {
        $('#editModal').fadeOut();
    });
});


$(document).on('click', '#saveEditBtn-question', function () {
    let id = $('#edit_id').val();
    let typename = $('#edit_typename').val();
    let description = $('#edit_description').val();
    let assessmenttype_id = $('#edit_assessmenttype_id').val();

    $.ajax({
        url: '/question-types/' + id,
        method: 'POST', // or use PUT if needed
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT', // method spoofing for Laravel PUT
            typename: typename,
            description: description,
            assessmenttype_id: assessmenttype_id
        },
        success: function (response) {
            $('#editModal').fadeOut();
            let row = $(`tr[data-id='${id}']`);
    
            row.find('.typename-cell').text(typename);
            row.find('.description-cell').text(description);
            row.find('.assessmenttype-cell').text(assessmenttype_id);
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


$(document).on('click', '.delete-questiontype-btn', function() {
    let id = $(this).data('id');
    let row = $(this).closest('tr'); // optional, for removing without reload
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    Swal.fire({
        title: 'Are you sure?',
        text: "This question type will be permanently deleted.",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/question-types/${id}`,
                type: 'DELETE',
                data: {
                    _token: csrfToken
                },
                success: function (response) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The question type has been removed.',
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


    $(document).ready(function () {
        $(document).on('click', '.btn-add', function () {
            $('#addModal').fadeIn();
        });
    
        $(document).on('click', '#closeAddModal', function () {
            $('#addModal').fadeOut();
        });
    
        $(document).on('click', '#saveNewBtn', function () {
            let typename = $('#new_typename').val();
            let description = $('#new_description').val();
            let assessmenttype_id = $('#new_assessmenttype_id').val();
            let csrfToken = $('#csrf_token').val();
    
            $.ajax({
                url: '/question-types', // ✅ POST to store route
                type: 'POST',
                data: {
                    _token: csrfToken,
                    typename: typename,
                    description: description,
                    assessmenttype_id: assessmenttype_id
                },
                success: function (response) {
                    $('#addModal').fadeOut();
                    Swal.fire({
                        icon: 'success',
                        title: 'Added!',
                        text: 'The question type was successfully added.',
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
                        title: 'Add Failed',
                        text: xhr.responseJSON?.message || 'An error occurred while updating.'
                    });
                    console.log("🚨 AJAX ERROR:", xhr.responseText);
                }
            });
        });
    });
    

