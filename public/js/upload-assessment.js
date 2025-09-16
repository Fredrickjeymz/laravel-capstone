$(document).ready(function () {
    // Show modal and set assessment_id
    $(document).on('click', '.btn-open-upload-modal', function () {
        const assessmentId = $(this).data('assessment-id');
        $('#assessment_id').val(assessmentId);
        $('#AssessmentUploadModal').fadeIn();
    });

    // Close modal
    $(document).on('click', '#closeAssessmentUploadModal', function () {
        $('#AssessmentUploadModal').fadeOut();
    });

    // Save upload
    $(document).on('click', '#saveAssessmentUploadModal', function () {
        let formData = {};
        $('#AssessmentUploadModal')
            .find('input, select')
            .each(function () {
                const name = $(this).attr('name');
                if (name) formData[name] = $(this).val();
            });

        formData._token = $('#csrf_token').val();

        $.ajax({
            url: '/assessment/upload',
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#AssessmentUploadModal').fadeOut();
                Swal.fire({
                    icon: 'success',
                    title: 'Uploaded!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: xhr.responseJSON?.message || 'Something went wrong.'
                });
                console.log("🚨 ERROR:", xhr.responseText);
            }
        });
    });

});



        