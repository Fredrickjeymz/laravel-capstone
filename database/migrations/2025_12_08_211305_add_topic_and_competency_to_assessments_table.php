<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTopicAndCompetencyToAssessmentsTable extends Migration
{
    public function up()
    {
        Schema::table('assessments', function (Blueprint $table) {
            // Use string(255) for short text; change to text() if you want longer content
            $table->string('topic')->nullable()->after('subject');
            $table->string('competency')->nullable()->after('topic');
        });
    }

    public function down()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['topic', 'competency']);
        });
    }
}
