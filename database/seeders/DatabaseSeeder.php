<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Scoreboard;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['Administrator', 'Moderator', 'Guest'];

        foreach ($roles as $role) {
            Role::factory()->create([
                "role" => $role
            ]);
        }

        // User::factory(20)->create();
        // Team::factory(10)->create();
        // Scoreboard::factory(5)->create();
        $categories = ['IT', 'Opce znanje', 'Filozofija', 'Povijest', 'Filmovi', 'Gaming'];

        foreach ($categories as $category) {
            Category::factory()->create([
                "name" => $category
            ]);
        }

        // Quiz::factory(20)->create();
        // QuestionType::factory(6)->create();
        // Question::factory(50)->create();
        // Answer::factory(100)->create();
    }
}
