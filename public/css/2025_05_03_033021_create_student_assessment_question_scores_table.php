<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_assessment_question_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_assessment_score_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessment_question_id')->constrained()->onDelete('cascade');
            $table->text('student_answer');
            $table->float('score_given');
            $table->float('max_score');
            $table->json('criteria_scores')->nullable();
            $table->timestamps();
        });
        
    }
    
    public function down(): void
    {
        Schema::dropIfExists('student_assessment_question_scores');
    }
};
