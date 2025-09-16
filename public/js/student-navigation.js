$(document).on("click", "#btn-dashboard, #btn-class, #btn-quiz", function (e) {
    e.preventDefault();
    let pageUrl = $(this).data("url");

    $(".navigation button").removeClass("active");
    $(this).addClass("active");
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

$(document).on("click", "#btn-quiz-quick", function (e) {
    e.preventDefault();
    let pageUrl = $(this).data("url");

    $(".navigation button").removeClass("active");
    $("#btn-quiz").addClass("active");
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

$(document).on("click", "#btn-class-quick", function (e) {
    e.preventDefault();
    let pageUrl = $(this).data("url");

    $(".navigation button").removeClass("active");
    $("#btn-class").addClass("active");
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