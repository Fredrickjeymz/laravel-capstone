<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\ArchivedTeacher;
use App\Models\Assessment;
use App\Models\ArchivedAssessment;
use App\Models\ArchivedAssessmentQuestion;
use Illuminate\Support\Facades\DB;
class ArchiveTeacherController extends Controller 
{
    public function archive($id)
    {
        DB::transaction(function () use ($id) {
            // Find the teacher
            $teacher = Teacher::findOrFail($id);

            // Move teacher to archived_teachers
            $archivedTeacher = ArchivedTeacher::create([
                'id' => $teacher->id,
                'fname' => $teacher->fname,
                'mname' => $teacher->mname,
                'lname' => $teacher->lname,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'birthdate' => $teacher->birthdate,
                'position' => $teacher->position,
                'gender' => $teacher->gender,
                'username' => $teacher->username,
                'password' => $teacher->password,
            ]);

            // Find all assessments of this teacher
            $assessments = Assessment::with('questions')->where('teacher_id', $teacher->id)->get();

            foreach ($assessments as $assessment) {
                // Archive the parent assessment
                $archivedAssessment = ArchivedAssessment::create([
                    'teacher_id' => $archivedTeacher->id, // Link to archived teacher
                    'title' => $assessment->title,
                    'subject' => $assessment->subject,
                    'instructions' => $assessment->instructions,
                    'question_type' => $assessment->question_type,
                    'rubric' => $assessment->rubric,
                ]);

                // Archive each question
                foreach ($assessment->questions as $question) {
                    ArchivedAssessmentQuestion::create([
                        'archived_assessment_id' => $archivedAssessment->id,
                        'question_text' => $question->question_text,
                        'options' => $question->options,
                        'answer_key' => $question->answer_key,
                        'sequence_number' => $question->sequence_number,
                    ]);
                }

                // Delete child questions first
                $assessment->questions()->delete();
                // Delete parent assessment
                $assessment->delete();
            }

            // Finally, delete teacher
            $teacher->delete();
        });

        return response()->json(['success' => true, 'message' => 'Teacher and related assessments archived successfully.']);
    }

    // Restore Teacher
    public function restore($id)
    {
        DB::transaction(function () use ($id) {
            $archivedTeacher = ArchivedTeacher::findOrFail($id);

            Teacher::create([
                'id' => $archivedTeacher->id, // 🛠 force same id
                'fname' => $archivedTeacher->fname,
                'mname' => $archivedTeacher->mname,
                'lname' => $archivedTeacher->lname,
                'email' => $archivedTeacher->email,
                'phone' => $archivedTeacher->phone,
                'birthdate' => $archivedTeacher->birthdate,
                'position' => $archivedTeacher->position,
                'gender' => $archivedTeacher->gender,
                'username' => $archivedTeacher->username,
                'password' => $archivedTeacher->password,
            ]);

            $archivedTeacher->delete();
        });

        return response()->json(['success' => true, 'message' => 'Teacher restored successfully.']);
    }

    // Delete Teacher Permanently
    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            // Find archived teacher
            $archivedTeacher = ArchivedTeacher::findOrFail($id);

            // Delete all related archived assessments and their questions
            $archivedAssessments = ArchivedAssessment::with('questions')->where('teacher_id', $archivedTeacher->id)->get();

            foreach ($archivedAssessments as $archivedAssessment) {
                // Delete archived questions
                $archivedAssessment->questions()->delete();
                // Delete archived assessment
                $archivedAssessment->delete();
            }

            // Delete archived teacher
            $archivedTeacher->delete();
        });

        return response()->json(['success' => true, 'message' => 'Teacher and assessments permanently deleted.']);
    }


}
