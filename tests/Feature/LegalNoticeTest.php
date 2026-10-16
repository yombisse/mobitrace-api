<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalNoticeTest extends TestCase
{
    public function test_legal_notice_is_publicly_available_for_the_mobile_consent_flow(): void
    {
        $this->get(route('legal.mentions'))
            ->assertOk()
            ->assertSee("Politique de confidentialité et conditions d'utilisation", false)
            ->assertSee('id="confidentialite"', false)
            ->assertSee('id="conditions"', false)
            ->assertSee('Équipe de développement Mobitrace', false);
    }
}
