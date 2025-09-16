$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Show modal
    $(document).on('click', '.btn-add-class', function () {
        $('#addModalClass').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeAddModalClass', function () {
        $('#addModalClass').fadeOut();
    });

    // Save class
    $(document).on('click', '#saveNewBtnClass', function () {
        let formData = {};

        $('#addModalClass')
            .find('input, select')
            .each(function () {
                const name = $(this).attr('name');
                if (name) formData[name] = $(this).val();
            });

        formData._token = $('#csrf_token').val();

        $.ajax({
            url: '/classes', // ✅ Adjusted to your class route
            type: 'POST',
            data: formData,
            success: function (response) {
            $('#addModalClass').fadeOut();

            Swal.fire({
                icon: 'success',
                title: 'Class Added!',
                text: 'The class was successfully added.',
                timer: 2000,
                showConfirmButton: false
            });

            let c = response.data; // ✅ the new class object

            let newRow = `
                <tr data-id="${c.id}">
                    <td class="classname-cell">${c.class_name}</td>
                    <td class="subject-cell">${c.subject ?? ''}</td>
                    <td class="yearlevel-cell">${c.year_level}</td>
                    <td>0</td> <!-- new class, so no students yet -->
                    <td>
                        <button class="btn btn-edit-class"
                            data-id="${c.id}"
                            data-name="${c.class_name}"
                            data-subject="${c.subject ?? ''}"
                            data-year="${c.year_level}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn delete-class-btn" data-id="${c.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="btn btn-view-students"
                            data-url="/classes/${c.id}/students">
                            View Students
                        </button>
                    </td>
                </tr>
            `;

            $(".question-table").prepend(newRow); // add at top of table
        },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Add Failed',
                    text: xhr.responseJSON?.message || 'An error occurred while adding the class.'
                });
                console.log("🚨 AJAX ERROR:", xhr.responseText);
            }
        });
    });
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


$(document).on('click', '.btn-edit-class', function () {
    const id = $(this).data('id');
    const className = $(this).data('name');
    const subject = $(this).data('subject');
    const yearLevel = $(this).data('year');

    $('#edit_class_id').val(id);
    $('#edit_class_name').val(className);
    $('#edit_subject').val(subject);
    $('#edit_year_level').val(yearLevel);

    $('#EditModalClass').fadeIn();
});

$(document).on('click', '#closeEditModalClass', function () {
    $('#EditModalClass').fadeOut();
});

$(document).on('click', '#editNewBtnClass', function () {
    const id = $('#edit_class_id').val();
    const formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        _method: 'PUT',
        class_name: $('#edit_class_name').val(),
        subject: $('#edit_subject').val(),
        year_level: $('#edit_year_level').val()
    };

    $.ajax({
        url: `/classes/${id}`, // Adjust if route uses a different URI
        method: 'POST',
        data: formData,
        success: function (response) {
        Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: 'The class was successfully updated.',
            timer: 2000,
            showConfirmButton: false
        });

        $('#EditModalClass').fadeOut();

        // Update the row in the table
        let row = $(`tr[data-id='${id}']`);
        row.find('.classname-cell').text(formData.class_name);
        row.find('.subject-cell').text(formData.subject);
        row.find('.yearlevel-cell').text(formData.year_level);

            // Optionally reload content via AJAX
            $.get(response.redirect, function () {
                let content = $('<div>').html().find('#content-area').html();
                $("#content-area").fadeOut(150, function () {
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

$(document).on('click', '.delete-class-btn', function() {
    let id = $(this).data('id');
    let row = $(this).closest('tr'); // optional, for removing without reload
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    Swal.fire({
        title: 'Are you sure?',
        text: "This class will be permanently deleted.",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/classes/${id}`,
                type: 'DELETE',
                data: {
                    _token: csrfToken
                },
                success: function (response) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The class has been removed.',
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