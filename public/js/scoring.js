document.addEventListener('click', function (e) {
    if (e.target.closest('#evaluate-btn-show')) {
        const modal = document.getElementById('evaluateModal');
        modal.style.display = 'block';
        // Let the browser render before applying transition
        setTimeout(() => modal.classList.add('show'), 10);
    }
});

document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'cancel-eval') {
        const modal = document.getElementById('evaluateModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); 
    }
});

$(document).ready(function () {
    console.log("✅ Evaluation script loaded!");

    $(document).on("click", "#evaluate-btn:not(:disabled)", function (e) {
        e.preventDefault();
        console.log("✅ Evaluate button clicked!");
        
        const $btn = $(this);
        $btn.prop("disabled", true).css("opacity", "0.7");

        const fileInput = document.getElementById("answer_file_input");
        const assessmentIdInput = document.getElementById("evaluation_assessment_id");
        const studentNameInput = document.getElementById("evaluation_student_name");

        if (!fileInput.files.length || !studentNameInput.value) {
            Swal.fire({
                icon: "warning",
                title: "Incomplete Submission",
                text: "Please complete all required fields.",
            });
            $btn.prop("disabled", false).css("opacity", "1");
            return;
        }

        const formData = new FormData();
        formData.append("assessment_id", assessmentIdInput.value);
        formData.append("student_name", studentNameInput.value);
        formData.append("answer_file", fileInput.files[0]);
        formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

        console.log("📤 Sending Evaluation FormData:", Object.fromEntries(formData));

        $("#overlay-spinner").show();

        $.ajax({
            url: "/evaluate-answers",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            success: function (data) {
                console.log("✅ Evaluation success response:", data);
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
            error: function (xhr) {
                console.error("❌ Evaluation error response:", xhr.responseText);
                let errorMsg = "Evaluation failed.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Please submit a file with a correct format."
                });
            },
            complete: function () {
                $("#overlay-spinner").hide();
                $btn.prop("disabled", false).css("opacity", "1");
            }
        });
    });
});

document.addEventListener("click", function(e){

    if (e.target.classList.contains("btn-expand")) {
        let studentId = e.target.dataset.id;
        let row = document.getElementById("assessments-" + studentId);

        // Toggle visibility
        if (row.style.display === "none") {
            row.style.display = "table-row";
            loadAssessments(studentId);
            e.target.textContent = "-";
        } else {
            row.style.display = "none";
            e.target.textContent = "+";
        }
    }

});

function loadAssessments(studentId) {
    let container = document.querySelector("#assessments-" + studentId + " .assessment-container");

    container.innerHTML = "<p>...</p>";

    fetch(`/student/${studentId}/scores`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = "<p>No assessment scores yet.</p>";
                return;
            }

            let html = "<ul class='assessment-list'>";

            data.forEach(score => {
                html += `
                    <div class="score-expansion">
                        <div class="assessment-list">
                            <div class="score-item-container">
                                <div class="score-item" data-score-id="${score.id}">
                                    <div class="score-item-title">TITLE: ${score.assessment.title}</div>
                                    <div class="score-item-value">SCORE: ${score.total_score}/${score.max_score}</div>
                                    <button class="btn-view-score" data-id="${score.id}">View</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += "</ul>";

            container.innerHTML = html;
        });
}

//make this an ajax call now to fetch the scoring breakdown view

document.addEventListener("click", function(e){
    if (e.target.classList.contains("btn-view-score")) {
        let id = e.target.dataset.id;

        $.ajax({
            url: `/saved-scoring-result-view/${id}`,
            type: "GET",
            success: function (response) {
                console.log("🟢 AJAX Success. Injecting content...");
                let extracted = $(response).find("#content-area").html();
                if (extracted) {
                        $("#content-area").fadeOut(150, function () {
                            $(this).html(extracted).fadeIn(150);
                        });
                }
            },
            error: function () {
                console.log("🔴 AJAX failed.");
                $("#breakdown-content").html("<p>Error loading content.</p>");
            }
        });
    }
});
