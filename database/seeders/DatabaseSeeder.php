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
        foreach ([
            ['nom' => 'Orange Money', 'code' => 'OM'],
            ['nom' => 'Moov Money', 'code' => 'MV'],
            ['nom' => 'Wave', 'code' => 'WA'],
            ['nom' => 'Sank Money', 'code' => 'SM'],
            ['nom' => 'Telecel Money', 'code' => 'TM'],
        ] as $reseau) {
            Reseau::firstOrCreate(['code' => $reseau['code']], $reseau);
        }

        User::firstOrCreate(
            ['telephone' => '70000000'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'code_agent' => 'AG-00001',
            ]
        );

        User::firstOrCreate(
            ['telephone' => '22606913191'],
            [
                'name' => 'Test User Burkina',
                'email' => 'testburkina@example.com',
                'code_agent' => 'AG-00002',
                'password' => bcrypt('069131'),
            ]
        );

        $this->call(TransactionSeeder::class);
    }
}
