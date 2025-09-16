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
    Schema::table('assessment_questions', function (Blueprint $table) {
        $table->text('answer_key')->change();
    });
}

public function down(): void
{
    Schema::table('assessment_questions', function (Blueprint $table) {
        $table->string('answer_key', 255)->change(); // fallback if you rollback
    });
}
};
