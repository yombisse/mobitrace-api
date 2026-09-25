<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScanCnibTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_scan_cnib_sans_recto_echoue()
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recto']);
    }

    public function test_fichier_non_image_echoue()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recto']);
    }

    public function test_image_trop_volumineuse_echoue()
    {
        $file = UploadedFile::fake()->image('cnib.jpg', 4000, 3000)->size(6000);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recto']);
    }

    public function test_format_non_supporte_echoue()
    {
        $file = UploadedFile::fake()->create('cnib.gif', 1000, 'image/gif');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recto']);
    }

    public function test_cle_gemini_absente_echoue()
    {
        config(['services.gemini.api_key' => null]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Configuration Gemini API manquante.',
            ]);
    }

    public function test_reponse_gemini_valide_retourne_donnees()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'KABORE',
                                        'prenoms' => 'Clovis',
                                        'date_naissance' => '1990-04-12',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CNIB',
                                        'numero_piece' => 'B12345678',
                                        'date_delivrance_piece' => '2020-01-15',
                                        'date_expiration_piece' => '2030-01-15',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Analyse CNIB effectuée avec succès.',
                'data' => [
                    'nom' => 'KABORE',
                    'prenoms' => 'Clovis',
                    'date_naissance' => '1990-04-12',
                    'nationalite' => 'Burkinabè',
                    'type_piece' => 'CNIB',
                    'numero_piece' => 'B12345678',
                    'date_delivrance_piece' => '2020-01-15',
                    'date_expiration_piece' => '2030-01-15',
                ],
            ]);
    }

    public function test_reponse_gemini_avec_champs_null()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => null,
                                        'prenoms' => null,
                                        'date_naissance' => null,
                                        'nationalite' => null,
                                        'type_piece' => null,
                                        'numero_piece' => null,
                                        'date_delivrance_piece' => null,
                                        'date_expiration_piece' => null,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nom' => null,
                    'prenoms' => null,
                    'date_naissance' => null,
                    'nationalite' => null,
                    'type_piece' => null,
                    'numero_piece' => null,
                    'date_delivrance_piece' => null,
                    'date_expiration_piece' => null,
                ],
            ]);
    }

    public function test_reponse_gemini_invalide_echoue()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [],
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'Réponse Gemini invalide.',
            ]);
    }

    public function test_erreur_http_gemini_echoue()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Invalid API key'], 401),
        ]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Erreur lors de la communication avec Gemini API.',
            ]);
    }

    public function test_json_invalide_echoue()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'ceci n\'est pas du json',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'Impossible de parser la réponse JSON.',
            ]);
    }

    public function test_acces_non_authentifie_refuse()
    {
        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(401);
    }

    public function test_recto_seul_fonctionne()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'KABORE',
                                        'prenoms' => 'Clovis',
                                        'date_naissance' => '1990-04-12',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CNIB',
                                        'numero_piece' => 'B12345678',
                                        'date_delivrance_piece' => '2020-01-15',
                                        'date_expiration_piece' => '2030-01-15',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $recto = UploadedFile::fake()->image('cnib_recto.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $recto]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nom' => 'KABORE',
                    'prenoms' => 'Clovis',
                ],
            ]);
    }

    public function test_recto_et_verso_fonctionne()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'DIALLO',
                                        'prenoms' => 'Aminata',
                                        'date_naissance' => '1985-06-20',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CIB-AES',
                                        'numero_piece' => 'B98765432',
                                        'date_delivrance_piece' => '2019-03-10',
                                        'date_expiration_piece' => '2029-03-10',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $recto = UploadedFile::fake()->image('cnib_recto.jpg');
        $verso = UploadedFile::fake()->image('cnib_verso.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', [
                'recto' => $recto,
                'verso' => $verso,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nom' => 'DIALLO',
                    'prenoms' => 'Aminata',
                    'type_piece' => 'CIB-AES',
                ],
            ]);
    }

    public function test_numero_b_visible_uniquement_au_verso()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'SANOU',
                                        'prenoms' => 'Fatou',
                                        'date_naissance' => '1992-11-05',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CIB-AES',
                                        'numero_piece' => 'B56789012',
                                        'date_delivrance_piece' => '2021-07-20',
                                        'date_expiration_piece' => '2031-07-20',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $recto = UploadedFile::fake()->image('cnib_recto.jpg');
        $verso = UploadedFile::fake()->image('cnib_verso.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', [
                'recto' => $recto,
                'verso' => $verso,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'numero_piece' => 'B56789012',
                ],
            ]);
    }

    public function test_identification_cnib_explicite()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'ZONGO',
                                        'prenoms' => 'Emmanuel',
                                        'date_naissance' => '1980-02-14',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CNIB',
                                        'numero_piece' => 'B34567890',
                                        'date_delivrance_piece' => '2014-09-01',
                                        'date_expiration_piece' => '2024-09-01',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $recto = UploadedFile::fake()->image('cnib_recto.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $recto]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type_piece' => 'CNIB',
                ],
            ]);
    }

    public function test_identification_cib_aes_explicite()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'KABORE',
                                        'prenoms' => 'Yasmine',
                                        'date_naissance' => '1995-08-30',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CIB-AES',
                                        'numero_piece' => 'B23456789',
                                        'date_delivrance_piece' => '2022-04-15',
                                        'date_expiration_piece' => '2032-04-15',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $recto = UploadedFile::fake()->image('cib_aes_recto.jpg');
        $verso = UploadedFile::fake()->image('cib_aes_verso.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', [
                'recto' => $recto,
                'verso' => $verso,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type_piece' => 'CIB-AES',
                ],
            ]);
    }

    public function test_type_piece_non_identifiable_retourne_null()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'TRAORE',
                                        'prenoms' => 'Paul',
                                        'date_naissance' => '1988-12-25',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => null,
                                        'numero_piece' => 'B12345678',
                                        'date_delivrance_piece' => '2017-06-10',
                                        'date_expiration_piece' => '2027-06-10',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $recto = UploadedFile::fake()->image('piece_id.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $recto]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type_piece' => null,
                ],
            ]);
    }

    public function test_dates_invalides_sont_normalisees_a_null()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => 'KABORE',
                                        'prenoms' => 'Clovis',
                                        'date_naissance' => '12/04/1990',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CNIB',
                                        'numero_piece' => 'B12345678',
                                        'date_delivrance_piece' => 'invalid-date',
                                        'date_expiration_piece' => '2030-01-15',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nom' => 'KABORE',
                    'prenoms' => 'Clovis',
                    'date_naissance' => null,
                    'date_delivrance_piece' => null,
                    'date_expiration_piece' => '2030-01-15',
                ],
            ]);
    }

    public function test_chaines_vides_sont_normalisees_a_null()
    {
        config(['services.gemini.api_key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'nom' => '',
                                        'prenoms' => '  ',
                                        'date_naissance' => '1990-04-12',
                                        'nationalite' => 'Burkinabè',
                                        'type_piece' => 'CNIB',
                                        'numero_piece' => 'B12345678',
                                        'date_delivrance_piece' => '2020-01-15',
                                        'date_expiration_piece' => '2030-01-15',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $file = UploadedFile::fake()->image('cnib.jpg');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/clients/scan-cnib', ['recto' => $file]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nom' => null,
                    'prenoms' => null,
                ],
            ]);
    }
}
