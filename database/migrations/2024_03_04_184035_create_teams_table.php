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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique()->nullable(false);
            $table->integer('points_earned')->default(0);
            $table->integer('capacity')->default(4);
            $table->integer('num_of_members')->default(0);
            $table->unsignedBigInteger('team_leader')->nullable();
            $table->boolean('is_currently_in_quiz')->default(false);
            $table->unsignedBigInteger('quiz_session_id')->nullable();
            $table->foreign('team_leader')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('quiz_session_id')->references('id')->on('quizzes')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
