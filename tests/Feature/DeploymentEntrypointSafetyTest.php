<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentEntrypointSafetyTest extends TestCase
{
    public function test_entrypoint_runs_non_destructive_migrations_and_reference_seeder_only(): void
    {
        $entrypoint = file_get_contents(base_path('docker-entrypoint.sh'));
        $dockerfile = file_get_contents(base_path('Dockerfile'));

        $this->assertIsString($entrypoint);
        $this->assertIsString($dockerfile);
        $this->assertStringContainsString('php artisan migrate --force', $entrypoint);
        $this->assertStringContainsString('php artisan db:seed --class=ReferenceDataSeeder --force', $entrypoint);
        $this->assertStringNotContainsString('migrate:fresh', $entrypoint);
        $this->assertStringNotContainsString('--seed', $entrypoint);
        $this->assertDoesNotMatchRegularExpression('/artisan\s+migrate:(fresh|refresh|reset)/', $dockerfile);
    }
}
