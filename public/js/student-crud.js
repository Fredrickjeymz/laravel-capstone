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
            success: function(response) {
                $('#addModalStudent').fadeOut();

                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'The student was successfully added.',
                    timer: 2000,
                    showConfirmButton: false
                });

                let s = response.data; // ✅ match your controller

                let birthdateFormatted = new Date(s.birthdate).toLocaleDateString('en-US', {
                    month: 'long',
                    day: '2-digit',
                    year: 'numeric'
                });

                let fullname = `${s.fname} ${s.mname ?? ''} ${s.lname}`;
                let genderFormatted = s.gender.charAt(0).toUpperCase() + s.gender.slice(1);

                let newRow = `
                    <tr data-id="${s.id}">
                        <td class="lrn-cell">${s.lrn}</td>
                        <td class="fullname-cell">${fullname}</td>
                        <td class="email-cell">${s.email}</td>
                        <td class="gender-cell">${genderFormatted}</td>
                        <td class="birthdate-cell">${birthdateFormatted}</td>
                    </tr>
                `;

                $(newRow).hide().prependTo(".student-table").fadeIn(300);
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
