$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Show modal
    $(document).on('click', '.btn-add-teacher', function () {
        $('#addModalTeacher').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeAddModalTeacher', function () {
        $('#addModalTeacher').fadeOut();
    });

    // Save student
    $(document).on('click', '#saveNewBtnTeacher', function () {
        let formData = {};

        $('#addModalTeacher')
            .find('input, select')
            .each(function () {
                const name = $(this).attr('name');
                if (name) formData[name] = $(this).val();
            });

        formData._token = $('#csrf_token').val();

        $.ajax({
            url: '/add-educator', // your actual route
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#addModalTeacher').fadeOut();

                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'The educator was successfully added.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Append new row directly
                let newRow = `
                    <tr data-id="${response.teacher.id}">
                        <td>${response.teacher.fname} ${response.teacher.mname ?? ''} ${response.teacher.lname}</td>
                        <td>${response.teacher.username}</td>
                        <td>${response.teacher.position}</td>
                        <td>${response.teacher.phone}</td>
                        <td>${response.teacher.gender}</td>
                        <td>${response.teacher.birthdate}</td>
                        <td><div class="active-stat">Active</div></td>
                        <td>
                            <button class="btn archive-teacher-btn" data-id="${response.teacher.id}">
                                Archive
                            </button>
                        </td>
                    </tr>
                `;
                $('#teachers-table tbody').prepend(newRow);

            },

            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Add Failed',
                    text: xhr.responseJSON?.message || 'An error occurred while adding the educator.'
                });
                console.log("🚨 AJAX ERROR:", xhr.responseText);
            }
        });
    });
});