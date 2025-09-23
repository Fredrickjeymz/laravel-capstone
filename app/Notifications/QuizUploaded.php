<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuizUploaded extends Notification
{
    use Queueable;

    protected $class;
    protected $teacher;
    protected $assessment;

    public function __construct($class, $teacher, $assessment)
    {
        $this->class = $class;
        $this->teacher = $teacher;
        $this->assessment = $assessment;
    }

    public function via($notifiable)
    {
        return ['database']; // store in DB
    }

    public function toDatabase($notifiable)
    {
        $teacherName = $this->teacher
            ? "{$this->teacher->fname} {$this->teacher->lname}"
            : "Teacher";

        return [
            'title'      => 'New Quiz Uploaded',
            'message'    => "{$teacherName} uploaded a new quiz '{$this->assessment->title}' for your class {$this->class->class_name} ({$this->class->year_level}), Subject: {$this->class->subject}.",
            'class_id'   => $this->class->id,
            'class_name' => $this->class->class_name,
            'year_level' => $this->class->year_level,
            'subject'    => $this->class->subject,
            'teacher'    => $teacherName,
            'assessment_id' => $this->assessment->id,
            'assessment_title' => $this->assessment->title,
        ];
    }
}


