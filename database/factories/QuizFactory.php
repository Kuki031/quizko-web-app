<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     *             $table->id();
            $table->string('name', 60)->unique()->nullable(false);
            $table->string('picture')->default('Nema slike.');
            $table->string('description')->default('Nema opisa.');
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_quiz_locked')->default(false);
            $table->dateTime('starts_at')->nullable(false);
            $table->dateTime('ends_at')->nullable(false);
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->text(60),
            'picture' => $this->faker->imageUrl(),
            'description' => $this->faker->text(200),
            'category_id' => $this->faker->numberBetween(1, 5),
            'is_quiz_locked' => $this->faker->boolean(),
            'starts_at' => $this->faker->dateTime(),
            'ends_at' => $this->faker->dateTime()
        ];
    }
}
