$(document).ready(function () {
    // Show modal
    $(document).on('click', '.btn-add-stud-class', function () {
        $('#addModalClassStudent').fadeIn(); // ✅ fixed ID
    });

    // Close modal
    $(document).on('click', '#closeAddModalClassStudent', function () {
        $('#addModalClassStudent').fadeOut(); // ✅ fixed ID
    });

    // Save student-class assignment
    $(document).on('click', '#saveNewBtnClassStudent', function () {
        let formData = {};

    let rawVal = $('#studentInput').val();

        // ✅ Extract only the integer ID (everything until first space)
        let studentId = rawVal.split(' ')[0];

        formData.student_id = studentId;
        formData.school_class_id = $('#addModalClassStudent select[name="school_class_id"]').val();
        formData._token = $('#csrf_token').val();

        formData._token = $('#csrf_token').val();

        $.ajax({
            url: '/students/classes',
            type: 'POST',
            data: formData,
            success: function (response) {
            $('#addModalClassStudent').fadeOut();

            Swal.fire({
                icon: 'success',
                title: 'Added!',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            });

            let s = response.student;
            let c = response.class;

            let newRow = `
                <tr data-id="${s.id}">
                    <td>${s.lrn}</td>
                    <td>${s.fname} ${s.mname ?? ''} ${s.lname}</td>
                    <td>${s.email}</td>
                    <td>${s.age}</td>
                    <td>${s.gender.charAt(0).toUpperCase() + s.gender.slice(1)}</td>
                    <td>
                        <ul style="margin:0; padding-left:20px;">
                            ${s.classes.map(cls => `<li>${cls.class_name} (${cls.year_level})</li>`).join('')}
                        </ul>
                    </td>
                </tr>
            `;

            $(".question-table").prepend(newRow); // insert on top
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
