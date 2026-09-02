<?php

namespace Database\Seeders;

use App\Domain\User\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = collect([
            ['name' => 'FMI Admin', 'email' => 'admin@fmi.test', 'role' => UserRole::Admin],
            ['name' => 'FMI Moderator', 'email' => 'moderator@fmi.test', 'role' => UserRole::Moderator],
            ['name' => 'Naledi Photographer', 'email' => 'naledi@fmi.test', 'role' => UserRole::User],
            ['name' => 'Thabo Photographer', 'email' => 'thabo@fmi.test', 'role' => UserRole::User],
        ])->mapWithKeys(function (array $attributes): array {
            $user = User::query()->updateOrCreate(
                ['email' => $attributes['email']],
                [
                    'name' => $attributes['name'],
                    'password' => 'password',
                    'role' => $attributes['role'],
                    'email_verified_at' => now(),
                ],
            );

            return [$attributes['email'] => $user];
        });
    }
}
