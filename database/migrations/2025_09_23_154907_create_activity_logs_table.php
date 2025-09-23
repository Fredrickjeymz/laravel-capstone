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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // the one who did the action
            $table->string('user_type')->nullable(); // e.g., Admin, Teacher
            $table->string('action'); // e.g., "Created Assessment"
            $table->text('description')->nullable(); // detailed info
            $table->ipAddress('ip_address')->nullable(); // user's IP
            $table->string('user_agent')->nullable(); // browser/device
            $table->timestamps();

            // optional foreign key if you want strict user relation
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
