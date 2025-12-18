<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // [新增] 必須產生 login_id，因為資料庫規定它是必填且唯一
            'login_id' => fake()->unique()->userName(),
            
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            

            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            
            // [建議] 加上預設角色，避免測試時漏填
            'role' => 'member',
        ];
    }
}