    $(document).ready(function () {
        console.log("✅ Navigation script loaded!");

        $(document).on("click", "#btn-return", function (e) {
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

    // Previous button click
    $(document).on("click", "#prev-btn:not(:disabled)", function (e) {
        e.preventDefault();
        let pageUrl = $(this).data("url");
        if (!pageUrl || pageUrl === '#') return;
        
        loadScoreContent(pageUrl);
    });

    // Next button click
    $(document).on("click", "#next-btn:not(:disabled)", function (e) {
        e.preventDefault();
        let pageUrl = $(this).data("url");
        if (!pageUrl || pageUrl === '#') return;
        
        loadScoreContent(pageUrl);
    });

    // Dropdown change
    $(document).on("change", "#assessmentDropdown", function () {
        let pageUrl = $(this).val();
        if (!pageUrl) return;
        
        loadScoreContent(pageUrl);
        $(this).val(''); // Reset dropdown
    });

    // Keyboard navigation
    $(document).on("keydown", function (e) {
        // Left arrow for previous
        if (e.key === 'ArrowLeft' && !$("#prev-btn").prop("disabled")) {
            e.preventDefault();
            $("#prev-btn").click();
        }
        // Right arrow for next
        if (e.key === 'ArrowRight' && !$("#next-btn").prop("disabled")) {
            e.preventDefault();
            $("#next-btn").click();
        }
    });

    // AJAX function to load score content
    function loadScoreContent(pageUrl) {
        console.log("🔄 Loading score content:", pageUrl);
        
        
        $.ajax({
            url: pageUrl,
            type: "GET",
            success: function (response) {
                console.log("✅ Score content loaded");
                
                // Extract the main content
                let $response = $(response);
                let scoreContent = $response.find("#content-area").html();
                let navigationData = {
                    title: $response.find("#current-title").text(),
                    position: $response.find("#current-position").text(),
                    date: $response.find("#current-date").text(),
                    prevUrl: $response.find("#prev-btn").data("url"),
                    nextUrl: $response.find("#next-btn").data("url"),
                    prevDisabled: $response.find("#prev-btn").prop("disabled"),
                    nextDisabled: $response.find("#next-btn").prop("disabled")
                };
                
                // Update content
                $("#content-area").html(scoreContent).fadeIn(150);
                
                // Update navigation
                $("#current-title").text(navigationData.title);
                $("#current-position").text(navigationData.position);
                $("#current-date").text(navigationData.date);
                $("#prev-btn").data("url", navigationData.prevUrl);
                $("#next-btn").data("url", navigationData.nextUrl);
                $("#prev-btn").prop("disabled", navigationData.prevDisabled);
                $("#next-btn").prop("disabled", navigationData.nextDisabled);
                
                // Update dropdown selection
                $("#assessmentDropdown option").each(function() {
                    $(this).prop("selected", $(this).val() === pageUrl);
                });
            },
            error: function (xhr, status, error) {
                console.log("❌ Failed to load score content:", error);
                $("#content-area").html(
                    '<div class="error-message">Error loading assessment. Please try again.</div>'
                ).fadeIn(150);
            }
        });
    }
    
});