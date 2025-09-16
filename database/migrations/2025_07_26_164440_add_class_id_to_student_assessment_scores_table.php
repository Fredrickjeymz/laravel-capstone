<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('student_assessment_scores', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id')->nullable()->after('student_id');
            // Add constraint after data cleanup
        });
    }


    public function down()
    {
        Schema::table('student_assessment_scores', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });
    }

};
