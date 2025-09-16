
$(document).ready(function () {
    console.log("✅ Navigation script loaded!");

    $(document).on("click", "#btn-assigned", function (e) {
        e.preventDefault();
        let pageUrl = $(this).data("url");

        $(this).addClass("active");

        $.ajax({
            url: pageUrl,
            type: "GET",
            success: function (response) {
                console.log("🟢 AJAX Success. Injecting content...");
                let extracted = $(response).find("#content-area").html();
                if (extracted) {
                    $("#content-area").fadeOut(150, function () {
                        $(this).html(extracted).fadeIn(150);
                    });
                } else {
                    console.log("🔴 No #main-area found in response.");
                    $("#content-area").html("<p>Error: Could not load content properly.</p>");
                }
            },
            error: function () {
                console.log("🔴 AJAX failed.");
                $("#content-area").html("<p>Error loading content.</p>");
            }
        });
    });
});

$(document).ready(function () {
    console.log("✅ Navigation script loaded!");

    $(document).on("click", ".btn-view-students", function (e) { 
        e.preventDefault();
        let pageUrl = $(this).data("url");

        $(this).addClass("active");

        $.ajax({
            url: pageUrl,
            type: "GET",
            success: function (response) {
                console.log("🟢 AJAX Success. Injecting content...");
                let extracted = $(response).find("#content-area").html();
                if (extracted) {
                    $("#content-area").fadeOut(150, function () {
                        $(this).html(extracted).fadeIn(150);
                    });
                } else {
                    console.log("🔴 No #content-area found in response.");
                    $("#content-area").html("<p>Error: Could not load content properly.</p>");
                }
            },
            error: function () {
                console.log("🔴 AJAX failed.");
                $("#content-area").html("<p>Error loading content.</p>");
            }
        });
    });
});

$(document).on('click', '.btn-remove-student', function () {
  const classId = $(this).data('class-id');
  const studentId = $(this).data('student-id');
  const row = $(this).closest('tr');

  Swal.fire({
    title: 'Are you sure?',
    text: "This student will be permanently removed.",
    showCancelButton: true,
    confirmButtonText: 'Yes, remove',
  }).then((res) => {
    if (!res.isConfirmed) return;

    $.ajax({
      url: '/classes/remove-student',
      method: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        class_id: classId,
        student_id: studentId
      },
      success: () => {
        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false });
        row.fadeOut(200, function(){ $(this).remove(); });
      },
      error: (xhr) => {
        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.error || 'Failed to remove.' });
      }
    });
  });
});
