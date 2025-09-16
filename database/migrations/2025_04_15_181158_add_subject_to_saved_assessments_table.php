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
        Schema::table('saved_assessments', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('title'); // adjust position as needed
        });
    }

    public function down(): void
    {
        Schema::table('saved_assessments', function (Blueprint $table) {
            $table->dropColumn('subject');
        });
    }
};
