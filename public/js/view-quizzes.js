$(document).on('click', '.view-quizzes-btn', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.ajax({
        url: `/student/class/${id}/quizzes`,
        method: 'GET',
        success: function (response) {
            console.log("🟢 AJAX Success. Injecting content...");
            let extracted = $(response).find("#content-area").html();

            if (extracted) {
                $("#content-area").fadeOut(150, function () {
                    $(this).html(extracted).fadeIn(150);
                });
            } else {
                console.log("🔴 No #content-area found in response.");
                $("#content-area").html("<p style='color:red;'>⚠️ Could not load quiz list.</p>");
            }
        },
        error: function (error) {
            console.error("❌ Failed to load quiz list:", error);
            $("#content-area").html("<p style='color:red;'>⚠️ Error loading quizzes.</p>");
        }
    });

    
    $('#btn-classes').addClass('active'); // adjust ID as needed
});


$(document).on('click', '.take-quiz-btn', function (e) {
    e.preventDefault();

    const url = $(this).attr('href') || `/student/quiz/${$(this).data('id')}`;

    Swal.fire({
        title: "Are you sure?",
        text: "The quiz will start immediately and the timer cannot be paused.",
        showCancelButton: true,
        confirmButtonText: "Yes, start quiz",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $("#content-area").fadeOut(150, function () {
                window.location.href = url;
            });
        }
    });
});

$(document).on('submit', '#quiz-form', function (e) {
    e.preventDefault();

    if (quizSubmitted) return;
    quizSubmitted = true;

    clearInterval(timerInterval); // ✅ Stops countdown if user clicks Submit

    let formData = new FormData(this);

    $.ajax({
        url: '/student/submit-quiz',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $("#overlay-spinner").show();
        },
        success: function (response) {
            $('#quiz-form').remove();

            const score = response.score;
            const message = `
                <div class="submitted-message">
                    <h2><i class="fas fa-check-circle"></i> Submission Successful!</h2>
                    <p>Your score is <strong>${score.total_score}/${score.max_score}</strong> (${score.percentage}%).</p>
                    ${score.remarks ? `<p class="remarks">Remarks: ${score.remarks}</p>` : ''}
                </div>
            `;
            $('.quiz-question-card').append(message); // Update container if needed
        },
        error: function (xhr) {
            quizSubmitted = false; 
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed!',
                text: '⚠️ Please check your internet connection and try again.',
                confirmButtonText: 'OK'
            });
        },
        complete: function () {
            $("#overlay-spinner").hide();
        }
    });
});





