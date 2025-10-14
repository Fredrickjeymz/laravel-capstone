function loadPreviewPage() {
    const spinner = $("#overlay-spinner");
    const content = $("#content-area");

    spinner.show();

    $.ajax({
        url: "/preview",
        method: "GET",
        success: function (response) {
            const newContent = $(response).find("#content-area").html();
            if (newContent) {
                content.fadeOut(150, function () {
                    $(this).html(newContent).fadeIn(150);
                    spinner.fadeOut(200);
                });
            } else {
                spinner.hide();
                console.error("❌ #content-area not found in response.");
            }
        },
        error: function () {
            spinner.hide();
            console.error("❌ Error loading preview.");
        }
    });
}

$(document).on("click", "#generate-btn", function (e) {
    e.preventDefault();
    const spinner = $("#overlay-spinner");
    const formData = new FormData();

    const file = $(".file-input")[0].files[0];
    const numQuestions = $(".number-input").val();
    const questionType = $(".dropdown-input").val();

    if (!file || !numQuestions || !questionType) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid!',
            text: 'Please complete all the requirements.',
        });
        return;
    }

    formData.append("title", $("input[name='title']").val());
    formData.append("subject", $("input[name='subject']").val());
    formData.append("instruction", $("input[name='instruction']").val());
    formData.append("learning_material", file);
    formData.append("question_type", questionType);
    formData.append("num_items", numQuestions);

    spinner.show();

    $.ajax({
        url: "/generateobjassessment",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        success: function (data) {
            if (data.redirect) {
                // Load preview via AJAX
                $.get(data.redirect, function (response) {
                    const tempDiv = $('<div>').html(response);
                    const newContent = tempDiv.find('#content-area').html();

                    $("#content-area").fadeOut(150, function () {
                        $(this).html(newContent).fadeIn(150, function () {
                            spinner.fadeOut(300);
                        });
                    });
                });
            } else {
                spinner.hide();
                Swal.fire({
                    icon: 'error',
                    title: 'No redirect URL',
                    text: 'The response did not contain a valid redirect.',
                });
            }
        },
        error: function (error) {
            spinner.hide();
            console.error("❌ Error generating:", error);
        }
    });
});
