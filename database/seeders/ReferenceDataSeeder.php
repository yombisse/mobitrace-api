<?php

namespace Database\Seeders;

use App\Models\Reseau;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'OM', 'nom' => 'Orange Money'],
            ['code' => 'MV', 'nom' => 'Moov Money'],
            ['code' => 'TM', 'nom' => 'Telecel Money'],
        ] as $reseau) {
            Reseau::updateOrCreate(
                ['code' => $reseau['code']],
                ['nom' => $reseau['nom']],
            );
        }
    }
}
