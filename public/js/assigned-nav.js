
$(document).ready(function () {
    console.log("✅ Navigation script loaded!");

    $(document).on("click", "#btn-assigned", function (e) {
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

    $(document).on("click", ".btn-view-students", function (e) { 
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
                    console.log("🔴 No #content-area found in response.");
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

$(document).on('click', '.btn-remove-student', function () {
  const classId = $(this).data('class-id');
  const studentId = $(this).data('student-id');
  const row = $(this).closest('tr');

  Swal.fire({
    title: 'Are you sure?',
    text: "This student will be permanently removed.",
    showCancelButton: true,
    confirmButtonText: 'Yes, remove',
  }).then((res) => {
    if (!res.isConfirmed) return;

    $.ajax({
      url: '/classes/remove-student',
      method: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        class_id: classId,
        student_id: studentId
      },
      success: () => {
        Swal.fire({ icon: 'success', title: 'Removed', timer: 1200, showConfirmButton: false });
        row.fadeOut(200, function(){ $(this).remove(); });
      },
      error: (xhr) => {
        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.error || 'Failed to remove.' });
      }
    });
  });
});

$(document).ready(function () {
    let currentClassId;

    // Show Add Student Modal and get class ID from button
    $(document).on('click', '.btn-add-to-class', function () {
        currentClassId = $(this).data('class-id');
        console.log('Current Class ID:', currentClassId); // Debug
        
        $('#addStudentToClassModal').fadeIn();
        $('#studentSelect').focus();
    });

    // Close Modal Events
    $(document).on('click', '#closeAddStudentModal, #cancelAddStudent', function () {
        $('#addStudentToClassModal').fadeOut();
        $('#studentSelect').val('');
    });

    // Click outside modal to close
    $(document).on('click', function (e) {
        if ($(e.target).hasClass('custom-modal')) {
            $('#addStudentToClassModal').fadeOut();
            $('#studentSelect').val('');
        }
    });

    // Add Student to Class
    $(document).on('click', '#saveStudentToClass', function () {
        let rawValue = $('#studentSelect').val().trim();
        
        console.log('Current Class ID:', currentClassId); // Debug
        console.log('Raw student value:', rawValue); // Debug
        
        if (!currentClassId) {
            Swal.fire('Error', 'Class ID is missing. Please try again.', 'error');
            return;
        }
        
        if (!rawValue) {
            Swal.fire('Error', 'Please select a student.', 'error');
            return;
        }

        // Extract student ID (format: "id | Name (LRN: number)")
        let studentId = rawValue.split('|')[0].trim();
        
        if (!studentId || isNaN(studentId)) {
            Swal.fire('Error', 'Please select a valid student from the list.', 'error');
            return;
        }

        let formData = {
            student_id: studentId,
            school_class_id: currentClassId,
            _token: $('#csrf_token').val()
        };

        console.log('Sending data:', formData);

        $.ajax({
            url: '/classes/add-student',
            type: 'POST',
            data: formData,
            beforeSend: function () {
                $('#saveStudentToClass').prop('disabled', true).text('Adding...');
            },
            success: function (response) {
                $('#addStudentToClassModal').fadeOut();
                $('#studentSelect').val('');
                $('#saveStudentToClass').prop('disabled', false).text('Add Student');

                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                let s = response.student;

                // Better date formatting that handles various formats
                let birthdate = formatDate(s.birthdate);

                let newRow = `
                    <tr data-id="${s.id}">
                        <td>${s.lrn}</td>
                        <td>${s.fname} ${s.mname ?? ''} ${s.lname}</td>
                        <td>${s.email}</td>
                        <td>${birthdate}</td>
                        <td>${s.gender.charAt(0).toUpperCase() + s.gender.slice(1)}</td>
                        <td>
                            <button class="btn btn-remove-student" 
                                    data-student-id="${s.id}" 
                                    data-class-id="${currentClassId}">
                                Remove
                            </button>
                        </td>
                    </tr>
                `;

                $("table.styled-table tbody").prepend(newRow);
                
                // Remove student from datalist
                $(`#availableStudents option[value*="${s.id} |"]`).remove();
                
                // If no students left in datalist, disable the input
                if ($('#availableStudents option').length === 0) {
                    $('#studentSelect').prop('disabled', true).attr('placeholder', 'No more students available to add');
                }
            },
            error: function (xhr) {
                $('#saveStudentToClass').prop('disabled', false).text('Add Student');
                
                let errorMessage = xhr.responseJSON?.message || 'An error occurred while adding the student.';
                Swal.fire('Error', errorMessage, 'error');
                console.log('Error details:', xhr.responseJSON);
            }
        });
    });

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        console.log('Original date string:', dateString); // Debug
        
        // Try different date parsing methods
        let date;
        
        // Method 1: Direct parsing
        date = new Date(dateString);
        
        // Method 2: If above fails, try parsing MySQL/ISO format
        if (isNaN(date.getTime())) {
            // Handle MySQL datetime format: "YYYY-MM-DD HH:MM:SS"
            let mysqlFormat = dateString.replace(' ', 'T');
            date = new Date(mysqlFormat);
        }
        
        // Method 3: If still fails, try splitting the date
        if (isNaN(date.getTime())) {
            let parts = dateString.split(/[- T]/);
            if (parts.length >= 3) {
                // Assume YYYY-MM-DD format
                date = new Date(parts[0], parts[1] - 1, parts[2]);
            }
        }
        
        // Method 4: If all else fails, return the original string
        if (isNaN(date.getTime())) {
            console.warn('Could not parse date:', dateString);
            return dateString;
        }
        
        // Format the date properly
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

// Search functionality
    $(document).on('input', '#searchClassStudents', function () {
        let searchText = $(this).val().toLowerCase();
        $('table.styled-table tbody tr').each(function () {
            let rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(searchText) > -1);
        });
    });
});