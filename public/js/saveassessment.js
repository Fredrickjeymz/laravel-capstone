$(document).ready(function () {
    console.log("✅ Assessment script loaded!");

    $(document).on("click", ".save", function (e) {
        e.preventDefault();
        
        let assessmentId = $(this).data("id");

        $.ajax({
            url: `/save-assessment/${assessmentId}`,
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Successfuy Saved!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            },
            error: function (xhr) {
                console.error("❌ Error saving assessment:", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: 'Failed to save assessment.',
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }            
        });
    });
});



$(document).ready(function () {
    console.log("✅ Assessment script loaded!");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.del', function () {
        let assessmentId = $(this).data('id');
    
        // Match the actual assessment container
        let container = $('.gen-del[data-id="' + assessmentId + '"]');
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This assessment will be deleted permanently.",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/assessments/' + assessmentId,
                    type: 'DELETE',
                    success: function (response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
    
                        container.fadeOut(300, function () {
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
});

$(document).ready(function () {
    console.log("✅ Assessment script loaded!");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.delprev', function () {
        let assessmentId = $(this).data('id');
    
        // Match the actual assessment container
        let container = $('.gen-del[data-id="' + assessmentId + '"]');
    
        Swal.fire({
            title: 'Are you sure?',
            text: "This assessment will be deleted permanently.",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/savedprevassessments/' + assessmentId,
                    type: 'DELETE',
                    success: function (response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
    
                        container.fadeOut(300, function () {
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
});

