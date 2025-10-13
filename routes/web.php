<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ObjectiveAssessmentController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Counts;
use App\Http\Controllers\GeneratedAssessments;
use App\Http\Controllers\AssessmentTypeController;
use App\Http\Controllers\QuestionTypeController;
use App\Http\Controllers\ArchiveAssessment;
use App\Http\Controllers\ArchiveTeacherController;
use App\Http\Controllers\AssessmentScoringController;
use App\Http\Controllers\SavedAssessmentScoringController;
use App\Http\Controllers\DashboardCotentsController;
use App\Http\Controllers\CountsAdmin;
use App\Http\Controllers\AdminStudent;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassAssignmentController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\AssessmentUploadController;
use App\Http\Controllers\QuizViewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssignedAssController;
use App\Http\Controllers\ChangePassController;
use App\Http\Controllers\ActivityLogController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::resource('teachers', App\Http\Controllers\TeacherController::class);

Route::get('/', function () {
    return view('Start');
});

Route::get('/stud-quiz', function () {
    return view('StudentQuiz');
})->name('stud-quiz');

Route::get('/stud-class', function () {
    return view('StudentClasses');
})->name('stud-class');

Route::get('/start', function () {
    return view('Start');
})->name('start');

Route::get('/createaccount', function () {
    return view('CreateAccount');
})->name('createaccount');

Route::get('/adminlogin', function () {
    return view('AdminLogin');
})->name('adminlogin');

Route::get('/login', function () {
    return view('Login');
})->name('login');

Route::get('/savedassessment', function () {
    return view('SavedAssessment');
})->name('savedassessment');

Route::get('/objective', function () {
    return view('Objective');
})->name('objective');

Route::get('/subjective', function () {
    return view('Subjective');
})->name('subjective');

Route::get('/teachers', function () {
    return view('TeachersManagement'); 
})->name('teachers');


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

//Educator 
Route::middleware(['teacher'])->group(function () {
    Route::get('/dashboard', [Counts::class, 'dashboard'])->middleware('auth')->name('teacherdashboard');
    Route::post('/generateobjassessment', [ObjectiveAssessmentController::class, 'generateAssessment']);
    Route::get('/preview', [ObjectiveAssessmentController::class, 'preview'])->name('preview');
    Route::post('/save-assessment/{id}', [AssessmentController::class, 'saveAssessment'])->name('assessment.save');
    Route::get('/my-saved-assessments', [AssessmentController::class, 'mySavedAssessments'])->name('my-saved-assessments');
    Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy'])->name('assessment.delete');
    Route::delete('/savedassessments/{id}', [AssessmentController::class, 'saveddestroy'])->name('savedassessment.delete');
    Route::get('/saved-assessment/view/{id}', [AssessmentController::class, 'show'])->name('saved-assessment.view');
    Route::delete('/savedprevassessments/{id}', [AssessmentController::class, 'savedprevdestroy'])->name('savedprevassessment.delete');
    Route::post('/evaluate-answers', [AssessmentScoringController::class, 'evaluateAnswers']);
    Route::get('/scoring-result/{score}', [AssessmentScoringController::class, 'scoringResult'])->name('scoring-result');
    Route::post('/saved-evaluate-answers', [SavedAssessmentScoringController::class, 'evaluateAnswers']);
    Route::get('/saved-scoring-result/{score}', [SavedAssessmentScoringController::class, 'scoringResult'])->name('saved-scoring-result');
    Route::get('/assessments/{assessment}/scores', [AssessmentController::class, 'showScores'])->name('assessment.scores');
    Route::get('/saved-scoring-result-view/{id}', [AssessmentController::class, 'viewSavedResult']);
    Route::delete('/score-destroy/{id}', [AssessmentController::class, 'scoredestroy'])->name('score-destroy');
    Route::get('/generateassessment', [AssessmentController::class, 'showAssessmentForm'])->name('generateassessment');
    Route::get('/classes', [ClassController::class, 'index'])->name('classes');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes');
    Route::put('/classes/{id}', [ClassController::class, 'update']);
    Route::delete('/classes/{id}', [ClassController::class, 'destroy'])->name('classes.destroy');
    Route::get('/students/classes', [ClassAssignmentController::class, 'index'])->name('students.classes');
    Route::post('/students/classes', [ClassAssignmentController::class, 'store'])->name('assign.student.to.class');
    Route::post('/assessment/upload', [AssessmentUploadController::class, 'store'])->name('assessment.upload');
    Route::get('/assigned-ass', [AssignedAssController::class, 'AssignedAss'])->name('assigned-ass');
    Route::post('/assigned-assessments/{id}/update-time', [AssessmentController::class, 'updateTime']);
    Route::delete('/assigned-assessments/{id}/delete-time', [AssessmentController::class, 'destroyTime'])->name('assigned-assessments.deleteTime');
    Route::get('/classes/{id}/students', [AssignedAssController::class, 'viewStudents'])->name('classes.viewStudents');
    Route::post('/classes/add-student', [AssignedAssController::class, 'addToClass'])->name('classes.add-student');
    Route::post('/classes/remove-student', [AssignedAssController::class, 'removeStudent'])->name('classes.remove-student');
    Route::post('/change-pass', [ChangePassController::class, 'changePassword'])->name('teacher.changePassword');
    Route::post('/edit-profile', [ChangePassController::class, 'updateProfile'])->name('teacher.updateProfile');
    Route::get('/teacher/change-password', function (){return view('teacher-change-password');})->name('teacher.change-password');
    Route::get('/teacher/activity-log', [ActivityLogController::class, 'index'])->name('teacher.activity-log');
    Route::get('/api/assessment/{id}/questions-only', function($id) {
        $assessment = \App\Models\Assessment::with('questions')->find($id);
        
        if (!$assessment) {
            return response()->json(['error' => 'Assessment not found'], 404);
        }
        
        $previousCount = request('previous_count', 0);
        $newQuestionsCount = $assessment->questions->count() - $previousCount;
        
        // Render the questions HTML directly (no partial view needed)
        $questionsHtml = '';
        foreach ($assessment->questions as $index => $question) {
            $cleaned_text = preg_replace('/^\d+[\.\)]\s*/', '', $question->question_text);
            $question_text = preg_split('/\s*[A-Z]\)[\s]*/', $cleaned_text)[0];
            preg_match_all('/\s*([A-Z])\)[\s]*(.*?)(?=\s*[A-Z]\)|$)/', $question->question_text, $matches);
            
            $questionsHtml .= '<li>';
            $questionsHtml .= '<p>' . trim($question_text) . '</p>';
            $questionsHtml .= '<p>';
            foreach ($matches[1] as $key => $option_letter) {
                $questionsHtml .= '<p>' . $option_letter . ') ' . trim($matches[2][$key]) . '</p>';
            }
            $questionsHtml .= '</p>';
            $questionsHtml .= '</li>';
        }
        
        return response()->json([
            'questions_html' => '<ol class="question-list">' . $questionsHtml . '</ol>',
            'status' => $assessment->status,
            'questions_count' => $assessment->questions->count(),
            'new_questions_count' => max(0, $newQuestionsCount),
            'is_completed' => $assessment->status === 'completed'
        ]);
    });
});

//Admin
Route::middleware(['admin'])->group(function () {
    Route::post('/add-educator', [AuthController::class, 'store'])->name('educator.store');
    Route::get('/teachers', [TeacherController::class, 'TeacherIndex'])->name('teachers');
    Route::get('/archivedteachers', [TeacherController::class, 'ArchivedTeacherIndex'])->name('archivedteachers');
    Route::get('/generated', [GeneratedAssessments::class, 'AssessmentIndex'])->name('generated');
    Route::get('/assessmenttype', [AssessmentTypeController::class, 'AssessmentTypeRestore'])->name('assessmenttype');
    Route::post('/update-assessment-type', [AssessmentTypeController::class, 'update']);
    Route::get('/questiontypes', [QuestionTypeController::class, 'QuestionTypeRestore'])->name('questiontypes');
    Route::get('/admin-assessment/view/{id}', [GeneratedAssessments::class, 'show'])->name('admin-assessment.view');
    Route::get('/archivedassessment', [GeneratedAssessments::class, 'Archivedshow'])->name('archivedassessment');
    Route::post('/archive-assessment/{id}', [ArchiveAssessment::class, 'archiveAssessment'])->name('archive.assessment');
    Route::post('/archive-teacher/{id}', [ArchiveTeacherController::class, 'archive']);
    Route::post('/restore-assessment/{id}', [ArchiveAssessment::class, 'restoreAssessment'])->name('restore.assessment');
    Route::delete('/delete-archived-assessment/{id}', [ArchiveAssessment::class, 'deleteArchivedAssessment'])->name('delete.archived-assessment');
    Route::post('/restore-teacher/{id}', [ArchiveTeacherController::class, 'restore'])->name('teacher.restore');
    Route::delete('/delete-teacher/{id}', [ArchiveTeacherController::class, 'delete'])->name('teacher.destroy');
    Route::get('/question-types', [QuestionTypeController::class, 'index'])->name('question-types.index');
    Route::post('/question-types', [QuestionTypeController::class, 'store'])->name('question-types.store');
    Route::put('/question-types/{id}', [QuestionTypeController::class, 'update']);
    Route::delete('/question-types/{id}', [QuestionTypeController::class, 'destroy'])->name('question-types.destroy');
    Route::get('/analytics/monthly-assessments', [DashboardCotentsController::class, 'getMonthlyAssessmentData']);
    Route::get('/admindashboard', [CountsAdmin::class, 'dashboard'])->name('admindashboard');
    Route::get('/students', [AdminStudent::class, 'index'])->name('students');
    Route::post('/students', [AdminStudent::class, 'store'])->name('students');
    Route::get('/admin/activity-log', [ActivityLogController::class, 'indexadmin'])
    ->name('admin.activity-log');
});

//Student
Route::middleware(['student'])->group(function () {
    Route::get('/my-classes', [StudentClassController::class, 'index'])->name('student.classes'); 
    Route::get('/student/class/{id}/quizzes', [QuizViewController::class, 'showQuizzes'])->name('student.class.quizzes');
    Route::get('/student/quiz/{id}', [QuizViewController::class, 'show'])->name('student.quiz.show');
    Route::get('/student/quizzes', [QuizViewController::class, 'showAllQuizzes'])->name('student.all-quizzes');
    Route::post('/student/submit-quiz', [QuizViewController::class, 'evaluateAnswers'])->name('student.quiz.submit');
    Route::get('/student/dashboard', [DashboardController::class, 'dashboard'])->name('stud-dash');
    Route::post('/change-pass-student', [ChangePassController::class, 'StudentchangePassword'])->name('student.changePassword');
    Route::post('/edit-profile-student', [ChangePassController::class, 'StudentupdateProfile'])->name('student.updateProfile');
    Route::get('/student/change-password', function (){return view('student-change-password');})->name('student.change-password');
    Route::get('/student/activity-log', [ActivityLogController::class, 'indexstudent'])
    ->name('student.activity-log');
    Route::get('/student/notifications', [ActivityLogController::class, 'indexnotification'])
    ->name('student.notifications');
});










