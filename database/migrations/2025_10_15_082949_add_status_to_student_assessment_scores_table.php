<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_assessment_scores', function (Blueprint $table) {
            $table->string('status')->default('completed'); // Add default for existing records
        });
    }

    public function down()
    {
        Schema::table('student_assessment_scores', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
