$(document).ready(function () {
    console.log("✅ Assessment script loaded!");

    $(document).on("click", "#btn-generate,", function (e) {
        e.preventDefault();
        let pageUrl = $(this).data("url");

        $(".assessment-buttons button").removeClass("active");
        $(this).addClass("active");

        $("#content-area").html("<p>Loading...</p>");

        $.ajax({
            url: pageUrl,
            type: "GET",
            success: function (response) {
                let newContent = $(response).find("#content-area").html();
                $("#content-area").html(newContent);
            },
            error: function () {
                $("#content-area").html("<p>Error loading content. Please try again.</p>");
            }
        });
    });
});
