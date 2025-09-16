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
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->text('question_text');
            $table->json('options')->nullable(); // only for multiple choice
            $table->longText('answer_key')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->timestamps();
    
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');

            
        });

      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
