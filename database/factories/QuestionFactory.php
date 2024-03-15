<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**

     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(),
            'points' => $this->faker->numberBetween(1, 3),
            'time_to_answer' => $this->faker->numberBetween(60, 120),
            'type_of_question_id' => $this->faker->numerify(1, 6),
            'quiz_id' => $this->faker->numberBetween(1, 20)
        ];
    }
}
