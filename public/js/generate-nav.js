$(document).ready(function () {
    console.log("✅ Navigation script loaded!");

    $(document).on("click", "#btn-generate-nav", function (e) {
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
                        $(this).html(extracted).fadeIn(150, function () {
                            // ✅ Re-initialize the Bloom sliders here after DOM update
                            if (typeof initializeBloomSliders === "function") {
                                initializeBloomSliders();
                            }
                        });
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
