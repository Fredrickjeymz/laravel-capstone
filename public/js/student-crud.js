$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Show modal
    $(document).on('click', '.btn-add-stud', function () {
        $('#addModalStudent').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeAddModalStudent', function () {
        $('#addModalStudent').fadeOut();
    });

    // Save student
    $(document).on('click', '#saveNewBtnStudent', function () {
        let formData = {};

        $('#addModalStudent')
            .find('input, select')
            .each(function () {
                const name = $(this).attr('name');
                if (name) formData[name] = $(this).val();
            });

        formData._token = $('#csrf_token').val();

        $.ajax({
            url: '/students', // your actual route
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#addModalStudent').fadeOut();

                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'The student was successfully added.',
                    timer: 2000,
                    showConfirmButton: false
                });

                let s = response.data; // ✅ this is the student object

                let newRow = `
                    <tr data-id="${s.id}">
                        <td class="lrn-cell">${s.lrn}</td>
                        <td class="fullname-cell">${s.fname} ${s.mname ?? ''} ${s.lname}</td>
                        <td class="email-cell">${s.email}</td>
                        <td class="gender-cell">${s.gender}</td>
                        <td class="birthdate-cell">${s.birthdate}</td>
                        <td>
                            <button class="btn">
                                Archive
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
                    text: xhr.responseJSON?.message || 'An error occurred while adding the student.'
                });
                console.log("🚨 AJAX ERROR:", xhr.responseText);
            }
        });
    });
});
