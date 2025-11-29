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

    clearInterval(timerInterval);

    let formData = new FormData(this);

    $.ajax({
        url: '/student/submit-quiz',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        timeout: 120000, // 2 minute timeout
        beforeSend: function () {
            $("#overlay-spinner").show();
            
            // ✅ Keep the header, hide form, show processing message
            $('#quiz-form').hide(); // Hide the form instead of removing
            $('.quiz-question-card').append(`
                <div class="processing-message mt-4">
                    <div class="alert alert-info">
                        <h4><i class="fas fa-robot"></i> AI Evaluation in Progress</h4>
                        <p class="mb-2">🤖 AI is carefully reviewing your answers...</p>
                        <p class="mb-0">This may take 20-30 seconds. Please don't close this page.</p>
                    </div>
                </div>
            `);
        },
        success: function (response) {
            // ✅ Remove processing message and show results
            $('.processing-message').remove();
            
            if (response.status === 'completed') {
                // Show immediate results
                const message = `
                    <div class="submitted-message mt-4">
                        <div class="alert alert-success">
                            <h4><i class="fas fa-check-circle"></i> Quiz Evaluated Successfully!</h4>
                        </div>
                        <div class="score-card mt-3 p-4 bg-light rounded">
                            <h5 class="text-primary">Your Results</h5>
                            <div class="score-details">
                                <p><strong>Score:</strong> ${response.score}/${response.max_score}</p>
                                <p><strong>Percentage:</strong> ${response.percentage}%</p>
                                <p><strong>Remarks:</strong> ${response.remarks}</p>
                            </div>
                        </div>
                        <p class="mt-3">📊 You can view detailed results in the <strong>Quizzes</strong> section.</p>
                    </div>
                `;
                $('.quiz-question-card').append(message);
            } else {
                // Fallback to queued message
                showQueuedMessage();
            }
        },
        error: function (xhr) {
            quizSubmitted = false;
            
            // ✅ Remove processing message on error
            $('.processing-message').remove();
            $('#quiz-form').show(); // Show the form again
            
            if (xhr.status === 504 || xhr.status === 0) {
                // Timeout or connection issue - fallback to background processing
                showQueuedMessage();
            } else {
                // Other errors
                let errorMessage = '⚠️ Please check your internet connection and try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed!',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            }
        },
        complete: function () {
            $("#overlay-spinner").hide();
        }
    });
});

function showQueuedMessage() {
    $('.processing-message').remove(); // Remove processing message
    $('.quiz-question-card').append(`
        <div class="submitted-message mt-4">
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle"></i> Submission Successful!</h4>
            </div>
            <p>🤖 AI is currently evaluating your quiz in the background.</p>
            <p>You can now safely exit this page. Later on, you'll be able to view your score in the <strong>Quizzes</strong> section.</p>
        </div>
    `);
}





