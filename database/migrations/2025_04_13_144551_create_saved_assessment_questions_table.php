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
        Schema::create('saved_assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saved_assessment_id');
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->longText('answer_key')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->timestamps();
        
            $table->foreign('saved_assessment_id')->references('id')->on('saved_assessments')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_assessment_questions');
    }
};
