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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->nullable(false);
            $table->integer('points')->default(1);
            $table->integer('time_to_answer')->default(60);
            $table->unsignedBigInteger('type_of_question_id');
            $table->unsignedBigInteger('quiz_id');

            $table->foreign('type_of_question_id')->references('id')->on('question_types')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question');
    }
};
