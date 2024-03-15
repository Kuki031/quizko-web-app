<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{

    protected $model = Role::class;

    public function definition(): array
    {
        $roles = ['Administrator', 'Moderator', 'Guest'];
        $role = array_shift($roles);

        return [
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
