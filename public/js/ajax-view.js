$(document).on('click', '.view-btn', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.ajax({
        url: `/saved-assessment/view/${id}`,
        method: 'GET',
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#content-area").html();
            if (extracted) {
                $("#content-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #content-area found in response.");
                $("#content-area").html("<p style='color:red;'>⚠️ Could not load assessment preview.</p>");
            }
        },
        error: function (error) {
            console.error("❌ Failed to load preview:", error);
            $("#content-area").html("<p style='color:red;'>⚠️ Error loading preview.</p>");
        }
    });

    // Highlight the sidebar tab
    $('.nav-link').removeClass('active');
    $('#btn-saved').addClass('active');
});


$(document).on('click', '.btn-scores-view', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.ajax({
        url: `/assessments/${id}/scores`, // ✅ Use the actual ID
        method: 'GET',
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#content-area").html();
            if (extracted) {
                $("#content-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #content-area found in response.");
                $("#content-area").html("<p style='color:red;'>⚠️ Could not load assessment preview.</p>");
            }
        },
        error: function (error) {
            console.error("❌ Failed to load preview:", error);
            $("#content-area").html("<p style='color:red;'>⚠️ Error loading preview.</p>");
        }
    });

    // Highlight the sidebar tab
    $('.nav-link').removeClass('active');
    $('#btn-saved').addClass('active');
});

$(document).on('click', '.view-btn-res', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.ajax({
        url: `/saved-scoring-result-view/${id}`, // ✅ Use the actual ID
        method: 'GET',
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#content-area").html();
            if (extracted) {
                $("#content-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #content-area found in response.");
                $("#content-area").html("<p style='color:red;'>⚠️ Could not load assessment preview.</p>");
            }
        },
        error: function (error) {
            console.error("❌ Failed to load preview:", error);
            $("#content-area").html("<p style='color:red;'>⚠️ Error loading preview.</p>");
        }
    });

    // Highlight the sidebar tab
    $('.nav-link').removeClass('active');
    $('#btn-saved').addClass('active');
});



$(document).ready(function () {
    console.log("✅ Assessment script loaded!");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.saveddel', function () {
        let assessmentId = $(this).data('id');
        let row = $(this).closest('tr'); // or .card, etc., if using card layout

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
                    url: '/savedassessments/' + assessmentId,
                    type: 'DELETE',
                    success: function (response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
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
});

$(document).ready(function () {
    console.log("✅ Assessment script loaded!");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.saveddel-res', function () {
        let assessmentId = $(this).data('id');
        let row = $(this).closest('tr'); // or .card, etc., if using card layout

        Swal.fire({
            title: 'Are you sure?',
            text: "This score record will be deleted permanently.",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/score-destroy/' + assessmentId,
                    type: 'DELETE',
                    success: function (response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
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
});




