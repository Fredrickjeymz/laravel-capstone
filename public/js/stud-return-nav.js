$(document).on("click", "#btn-return-class", function (e) {
    e.preventDefault();
    let pageUrl = $(this).data("url");

    $.ajax({
        url: pageUrl,
        type: "GET",
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#student-content-area").html();
            if (extracted) {
                $("#student-content-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #main-area found in response.");
                $("#student-content-area").html("<p>Error: Could not load content properly.</p>");
            }
        },
        error: function () {
            console.log("🔴 AJAX failed.");
            $("#student-content-area").html("<p>Error loading content.</p>");
        }
    });
});