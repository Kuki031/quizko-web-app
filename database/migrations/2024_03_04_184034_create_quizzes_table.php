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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique()->nullable(false);
            $table->string('picture')->nullable()->default('Nema slike.');
            $table->string('description')->default('Nema opisa.');
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_quiz_locked')->default(false);
            $table->dateTime('starts_at')->nullable(false);
            $table->dateTime('ends_at')->nullable(false);
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
