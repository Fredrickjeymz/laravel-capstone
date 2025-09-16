<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saved_student_assessment_question_scores', function (Blueprint $table) {
            $table->id();
        
            // Foreign key to saved_student_assessment_scores table
            $table->foreignId('saved_student_assessment_score_id')
                ->constrained()
                ->onDelete('cascade');
        
            // Foreign key to saved_assessment_questions table
            $table->foreignId('saved_assessment_question_id')
                ->constrained()
                ->onDelete('cascade');
        
            $table->text('student_answer')->nullable();
        
            // Store score given to the student's answer
            $table->float('score_given')->default(0);
        
            // Store max score possible for this question
            $table->float('max_score')->default(1);
        
            // Optional: store rubric criteria breakdown as JSON
            $table->json('criteria_scores')->nullable();
        
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_student_assessment_question_scores');
    }
};
