<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentAddedToClass extends Notification
{
    use Queueable;

    protected $class;
    protected $teacher;

    public function __construct($class, $teacher)
    {
        $this->class = $class;
        $this->teacher = $teacher;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // ✅ Check teacher is not null
        $teacherName = $this->teacher
            ? "{$this->teacher->fname} {$this->teacher->lname}"
            : "Teacher";

        return [
            'title'      => 'Added to Class',
            'message'    => "You were added to {$this->class->class_name} ({$this->class->year_level}), Subject: {$this->class->subject} by {$teacherName}.",
            'class_id'   => $this->class->id,
            'class_name' => $this->class->class_name,
            'year_level' => $this->class->year_level,
            'subject'    => $this->class->subject,
            'teacher'    => $teacherName,
        ];
    }
}

