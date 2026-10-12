<?php

namespace Tests\Unit;

use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    public function test_database_seeder_only_calls_reference_data_seeder(): void
    {
        $source = file_get_contents(database_path('seeders/DatabaseSeeder.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('ReferenceDataSeeder::class', $source);
        $this->assertStringNotContainsString('User', $source);
        $this->assertStringNotContainsString('TransactionSeeder', $source);
    }
}
