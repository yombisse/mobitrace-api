<?php

namespace Database\Seeders;

use App\Models\Reseau;
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
        // User::factory(10)->create();
        Reseau::factory()->createMany([
            ['nom' => 'Orange Money', 'code' => 'OM'],
            ['nom' => 'Moov Money', 'code' => 'MV'],
            ['nom' => 'Wave', 'code' => 'WA'],
            ['nom' => 'Sank Money', 'code' => 'SM'],
            ['nom' => 'Telecel Money', 'code' => 'TM'],
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'telephone' => '70000000',
            'code_agent' => 'AG-00001',
        ]);
    }
}
