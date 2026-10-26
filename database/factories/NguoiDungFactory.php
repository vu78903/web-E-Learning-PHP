<?php

namespace Database\Factories;

use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<NguoiDung>
 */
class NguoiDungFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '09'.fake()->unique()->numerify('########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => NguoiDung::ROLE_STUDENT,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => NguoiDung::ROLE_ADMIN]);
    }

    public function instructor(): static
    {
        return $this->state(fn () => ['role' => NguoiDung::ROLE_INSTRUCTOR]);
    }

    public function student(): static
    {
        return $this->state(fn () => ['role' => NguoiDung::ROLE_STUDENT]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
