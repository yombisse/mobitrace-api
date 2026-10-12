<?php

namespace Tests\Feature;

use Tests\TestCase;

class SanctumScheduleTest extends TestCase
{
    public function test_expired_token_pruning_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('sanctum:prune-expired --hours=24')
            ->assertExitCode(0);
    }
}
