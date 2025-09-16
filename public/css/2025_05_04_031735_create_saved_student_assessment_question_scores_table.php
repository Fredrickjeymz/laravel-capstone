<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_student_assessment_question_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_student_assessment_score_id')->constrained()->onDelete('cascade');
            $table->foreignId('saved_assessment_question_id')->constrained()->onDelete('cascade');
            $table->text('student_answer');
            $table->float('score_given');
            $table->float('max_score');
            $table->json('criteria_scores')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_student_assessment_question_scores');
    }
};
