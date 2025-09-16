console.log("✅ Navigation script loaded!");

$(document).on("click", "#btn-return-home, #btn-createaccount, #btn-adminlogin, #btn-teacherlogin", function (e) {
    e.preventDefault();
    let pageUrl = $(this).data("url");
    console.log("🟡 Clicked button. Will load:", pageUrl);

    $.ajax({
        url: pageUrl,
        type: "GET",
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#main-area").html();
            if (extracted) {
                $("#main-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #main-area found in response.");
                $("#main-area").html("<p>Error: Could not load content properly.</p>");
            }
        },
        error: function () {
            console.log("🔴 AJAX failed.");
            $("#main-area").html("<p>Error loading content.</p>");
        }
    });
});
