$(document).ready(function () {
    console.log("✅ Assessment script loaded!");

    $(document).on("click", ".archive-btn", function (e) {
        e.preventDefault();
        
        let assessmentId = $(this).data("id");

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to archive this assessment.",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/archive-assessment/${assessmentId}`,
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Archived Successfully!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });

                        // Optional: remove the row without reloading
                        $(e.target).closest('tr').fadeOut(500, function() { $(this).remove(); });
                    },
                    error: function (xhr) {
                        console.error("❌ Error archiving assessment:", xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: 'Failed to archive assessment.',
                            timer: 2000,
                            showConfirmButton: false,
                            timerProgressBar: true
                        });
                    }
                });
            }
        });
    });
});

$(document).on("click", ".archive-teacher-btn", function (e) {
    e.preventDefault();

    let teacherId = $(this).data("id");

    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to archive this teacher.",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/archive-teacher/${teacherId}`,
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archived Successfully!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });

                    // Optionally fadeout the row
                    $(e.target).closest('tr').fadeOut(500, function() { $(this).remove(); });
                },
                error: function (xhr) {
                    console.error("❌ Error archiving teacher:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: 'Failed to archive teacher.',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            });
        }
    });
});

$(document).on('click', '.restore-btn', function (e) {
    e.preventDefault();
    let assessmentId = $(this).data('id');
    let $row = $(this).closest('tr');

    Swal.fire({
        title: 'Restore Assessment?',
        text: "This will move it back to active assessments.",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, restore!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/restore-assessment/${assessmentId}`,
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (response) {
                    Swal.fire('Restored!', response.message, 'success');
                    $row.fadeOut(500, function () { $(this).remove(); });
                },
                error: function (xhr) {
                    Swal.fire('Failed!', 'Failed to restore assessment.', 'error');
                }
            });
        }
    });
});

$(document).on('click', '.delete-btn', function (e) {
    e.preventDefault();
    let assessmentId = $(this).data('id');
    let $row = $(this).closest('tr');

    Swal.fire({
        title: 'Delete Permanently?',
        text: "This action cannot be undone!",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete permanently!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/delete-archived-assessment/${assessmentId}`,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function (response) {
                    Swal.fire('Deleted!', response.message, 'success');
                    $row.fadeOut(500, function () { $(this).remove(); });
                },
                error: function (xhr) {
                    Swal.fire('Failed!', 'Failed to delete assessment.', 'error');
                }
            });
        }
    });
});

$(document).ready(function () {
    console.log("✅ Archive Teacher Management Script Loaded!");

    // Restore Teacher
    $(document).on('click', '.restore-teacher-btn', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to restore this teacher?",
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Restore'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/restore-teacher/${id}`,
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire('Restored!', response.message, 'success');
                        // Remove row or reload table
                        $(`button[data-id="${id}"]`).closest('tr').remove();
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire('Error!', 'Failed to restore teacher.', 'error');
                    }
                });
            }
        });
    });

    // Delete Teacher Permanently
    $(document).on('click', '.delete-teacher-btn', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the teacher!",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete Permanently'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/delete-teacher/${id}`,
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        // Remove row or reload table
                        $(`button[data-id="${id}"]`).closest('tr').remove();
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire('Error!', 'Failed to delete teacher.', 'error');
                    }
                });
            }
        });
    });
});

