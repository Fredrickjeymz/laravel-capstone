function loadPreviewPage() {
    const scrollPos = window.pageYOffset || document.documentElement.scrollTop;
    
    $.ajax({
        url: "/preview",
        method: "GET",
        success: function (response) {
            console.log("🟢 Preview loaded successfully.");
            $('#btn-dashboard').removeClass('active');
            $('#btn-generate').addClass('active');

            let newContent = $(response).find("#content-area").html();

            if (newContent) {
                // Fade out, update, fade in - but preserve scroll
                $("#content-area").fadeOut(150, function () {
                    $(this).html(newContent).fadeIn(150, function() {
                        // Restore scroll position after fade in completes
                        window.scrollTo(0, scrollPos);
                        console.log("🔁 AJAX preview loaded — reinitializing watcher.");
                        initPreviewWatcher();
                    });
                });
            } else {
                console.error("❌ Could not find #content-area in response.");
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Preview load failed:", error);
            // Still restore scroll position even on error
            window.scrollTo(0, scrollPos);
        }
    });
}

function initPreviewWatcher() {
    const spinner = $("#overlay-spinner");
    const content = $("#assessment-content");
    const assessmentId = content.data("id");
    const assessmentStatus = content.data("assessment-status");

    // 🔥 ADD THIS CHECK - Only run if we're on a preview page with assessment
    if (!assessmentId || !content.length) {
        console.log("⚠️ No assessment found or not on preview page. Skipping watcher.");
        spinner.hide(); // Ensure spinner is hidden
        return;
    }

    console.log("🔍 Initializing preview watcher for assessment:", assessmentId, "status:", assessmentStatus);

    if (assessmentStatus === "processing" || assessmentStatus === "pending" || assessmentStatus === "in-progress") {
        spinner.show();
        content.hide();

        const checkInterval = setInterval(() => {
            console.log("⏳ Checking assessment status...");
            $.ajax({
                url: `/check-assessment-status/${assessmentId}`,
                method: "GET",
                success: function (response) {
                    console.log("🟢 Status response:", response);
                    if (response.status === "completed") {
                        clearInterval(checkInterval);
                        console.log("✅ Assessment ready! Reloading preview...");

                        $.ajax({
                            url: `/preview?id=${assessmentId}`,
                            method: "GET",
                            success: function (html) {
                                const newContent = $(html).find("#content-area").html();
                                $("#content-area").fadeOut(200, function () {
                                    $(this).html(newContent).fadeIn(200, initPreviewWatcher); // re-init watcher after load
                                });
                            }
                        });
                    }
                },
                error: function (err) {
                    console.error("❌ Error checking status:", err);
                }
            });
        }, 3000);
    } else {
        spinner.hide();
        content.show();
    }
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

    // Re-run preview watcher every time preview loads via AJAX
    $(document).ajaxSuccess(function (event, xhr, settings) {
        if (settings.url.includes("/preview")) {
            console.log("🔁 AJAX preview loaded — reinitializing watcher.");
       
        }
    });

    $(document).on("click", "#generate-btn", function (e) {
        e.preventDefault();
        console.log("✅ Generate button clicked!");

        const formData = new FormData();
        const file = document.querySelector(".file-input").files[0];
        const numQuestions = document.querySelector(".number-input").value;
        const questionType = document.querySelector(".dropdown-input").value;
        const numOptions = document.querySelector(".option-num")?.value || null;
        //const bloom = {
            //remember: $("input[name='bloom[remember]']").val(),
            //understand: $("input[name='bloom[understand]']").val(),
            //apply: $("input[name='bloom[apply]']").val(),
            //analyze: $("input[name='bloom[analyze]']").val(),
            //evaluate: $("input[name='bloom[evaluate]']").val(),
            //create: $("input[name='bloom[create]']").val(),
        //};

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

        const quarter = document.querySelector("select[name='quarter']").value;
        const subject = document.querySelector("select[name='subject']").value;

        if (!quarter || !subject) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid!',
                text: 'Please select both Quarter and Subject.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        formData.append("quarter", quarter);
        formData.append("subject", subject);
        formData.append("instruction", document.querySelector("input[name='instruction']").value);
        formData.append("learning_material", file);
        formData.append("question_type", questionType);
        formData.append("num_items", numQuestions);
        //formData.append("bloom_taxonomy", JSON.stringify(bloom));
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
                if (data.assessment_id) {
                    const previewUrl = `/preview/${data.assessment_id}`;
                    console.log("🔄 Loading preview for new assessment:", previewUrl);

                    $.get(previewUrl, function (response) {
                        let tempDiv = $('<div>').html(response);
                        let content = tempDiv.find('#content-area').html();
                    
                        $("#content-area").fadeOut(150, function () {
                            $(this).html(content).fadeIn(150, function() {
                                console.log("🔁 Reinitializing preview watcher after generation redirect...");
                                // 🔥 ADD SMALL DELAY TO ENSURE DOM IS READY
                                setTimeout(() => {
                                    $("#overlay-spinner").hide(); // Ensure spinner is hidden
                                    initPreviewWatcher();
                                }, 500);
                            });
                        });
                    });
                }
            },          
            error: function (error) {
                console.error("❌ Error response:", error);
                $("#overlay-spinner").hide();

                let errMsg = "⚠️ An unknown error occurred while generating the assessment.";

                if (error.responseJSON) {
                    const data = error.responseJSON;
                    errMsg = data.error || data.message || JSON.stringify(data);
                    console.error("📋 Server details:", data);
                } else {
                    console.error("📋 Raw error:", error);
                }

                Swal.fire({
                    icon: 'error',
                    title: 'An error occurred',
                    html: `<p>${errMsg}</p>`,
                    confirmButtonText: 'OK'
                });

                $(".generated-area").html(`<p style='color:red;'>${errMsg}</p>`);
            }
        });
    });
});
