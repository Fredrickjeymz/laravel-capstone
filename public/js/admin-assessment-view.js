$(document).on('click', '.view-btn', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.ajax({
        url: `/admin-assessment/view/${id}`,
        method: 'GET',
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#admin-content-area").html();
            if (extracted) {
                $("#admin-content-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #content-area found in response.");
                $("#admin-content-area").html("<p style='color:red;'>⚠️ Could not load assessment preview.</p>");
            }
        },
        error: function (error) {
            console.error("❌ Failed to load preview:", error);
            $("#admin-content-area").html("<p style='color:red;'>⚠️ Error loading preview.</p>");
        }
    });

    // Highlight the sidebar tab
});