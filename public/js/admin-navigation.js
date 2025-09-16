$(document).on("click", "#btn-teachers, #btn-students, #btn-dashboard, #btn-generated, #btn-questions, #btn-archive", function (e) {
    e.preventDefault();
    let pageUrl = $(this).data("url");

    $(".navigation button").removeClass("active");
    $(this).addClass("active");

    // Loading content

    $.ajax({
        url: pageUrl,
        type: "GET",
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#admin-content-area").html();
            if (extracted) {
                $("#admin-content-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #main-area found in response.");
                $("#admin-content-area").html("<p>Error: Could not load content properly.</p>");
            }
        },
        error: function () {
            console.log("🔴 AJAX failed.");
            $("#admin-content-area").html("<p>Error loading content.</p>");
        }
    });
});
