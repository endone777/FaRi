<?php

namespace Database\Seeders;

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
        User::updateOrCreate(
            ['email' => 'admin@fari.test'],
            ['name' => 'Администратор', 'password' => 'password', 'is_admin' => true],
        );

        $this->call([
            DeliveryMethodSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
