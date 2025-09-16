<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_student_assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_assessment_id')->constrained()->onDelete('cascade');
            $table->string('student_name');
            $table->string('student_file_path');
            $table->float('total_score');
            $table->float('max_score');
            $table->float('percentage');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_student_assessment_scores');
    }
};
