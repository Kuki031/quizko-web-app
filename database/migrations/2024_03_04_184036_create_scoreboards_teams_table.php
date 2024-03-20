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
        Schema::create('scoreboards_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('scoreboard_id')->nullable();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('scoreboard_id')->references('id')->on('scoreboards')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['team_id', 'scoreboard_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoreboards_teams');
    }
};
