
function loadPreviewPage() {
    $.ajax({
        url: "/preview",
        method: "GET",
        success: function (response) {
            console.log("🟢 Preview loaded successfully.");
            $('#btn-dashboard').removeClass('active');
            $('#btn-generate').addClass('active');

            let newContent = $(response).find("#content-area").html();

            if (newContent) {
                $("#content-area").fadeOut(150, function () {
                    $(this).html(newContent).fadeIn(150);
                });
            } else {
                console.error("❌ Could not find #content-area in response.");
                $("#content-area").html("<p style='color:red;'>⚠️ Could not load preview content.</p>");
            }
        },
    });
}


$(document).ready(function () {
    console.log("✅ Navigation script loaded!");

    const lastActive = localStorage.getItem("activeNav");
    if (lastActive) {
        $(".nav-btn").removeClass("active");
        $(`#nav-${lastActive}`).addClass("active");
    }

    $(document).on("click", "a[href='/preview']", function (e) {
        e.preventDefault();
        loadPreviewPage();
    });

    $(document).on("click", "#generate-btn", function (e) {
        e.preventDefault();
        console.log("✅ Generate button clicked!");

        const formData = new FormData();
        const file = document.querySelector(".file-input").files[0];
        const numQuestions = document.querySelector(".number-input").value;
        const questionType = document.querySelector(".dropdown-input").value;
        const numOptions = document.querySelector(".option-num")?.value || null;
        const bloom = {
            remember: $("input[name='bloom[remember]']").val(),
            understand: $("input[name='bloom[understand]']").val(),
            apply: $("input[name='bloom[apply]']").val(),
            analyze: $("input[name='bloom[analyze]']").val(),
            evaluate: $("input[name='bloom[evaluate]']").val(),
            create: $("input[name='bloom[create]']").val(),
        };

        if (!file || !numQuestions || !questionType) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid!',
                text: 'Please complete all the requirements.',
                timer: 2000,
                showConfirmButton: false
                });
            return;
        }

        formData.append("title", document.querySelector("input[name='title']").value);
        formData.append("subject", document.querySelector("input[name='subject']").value);
        formData.append("instruction", document.querySelector("input[name='instruction']").value);
        formData.append("learning_material", file);
        formData.append("question_type", questionType);
        formData.append("num_items", numQuestions);
        formData.append("bloom_taxonomy", JSON.stringify(bloom));
        if (questionType === "multiplechoice") {
            formData.append("num_options", numOptions);
        }

        console.log("📤 Sending FormData:", [...formData.entries()]);
        $("#overlay-spinner").show();

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
                console.log("✅ Success response:", data);
                if (data.redirect) {
                    $.get(data.redirect, function (response) {
                        let tempDiv = $('<div>').html(response);
                        let content = tempDiv.find('#content-area').html();
                    
                        $("#content-area").fadeOut(150, function () {
                            $(this).html(content).fadeIn(150);
                        });
                    });                 
                }
                
            },            
            error: function (error) {
                console.error("❌ Error response:", error);
                $("#overlay-spinner").hide(); // ✅ Stop spinner

                Swal.fire({
                    icon: 'error',
                    title: 'An error occurred',
                    text: '⚠️ Please check your internet connection and try again.',
                    confirmButtonText: 'OK'
                });

                if (error.status === 422) {
                    $(".generated-area").html("<p style='color:red;'>⚠️ Validation failed. Check your inputs.</p>");
                } else {
                    $(".generated-area").html("<p style='color:red;'>⚠️ An error occurred while generating the assessment.</p>");
                }
            }
        });
    });
});
