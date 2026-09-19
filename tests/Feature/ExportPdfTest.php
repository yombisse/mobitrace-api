<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;

    private User $user2;

    private Reseau $reseau;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create([
            'telephone' => '70000001',
            'password' => bcrypt('password'),
        ]);

        $this->user2 = User::factory()->create([
            'telephone' => '70000002',
            'password' => bcrypt('password'),
        ]);

        $this->reseau = Reseau::factory()->create([
            'code' => 'OM',
            'nom' => 'Orange Money',
        ]);

        $this->client = Client::factory()->create([
            'user_id' => $this->user1->id,
            'telephone' => '70000003',
        ]);
    }

    public function test_export_requires_authentication()
    {
        $response = $this->getJson('/api/v1/transactions/export?debut=2026-01-01&fin=2026-01-31');

        $response->assertStatus(401);
    }

    public function test_export_success_returns_pdf()
    {
        $today = now();
        $startDate = $today->copy()->subDays(5)->format('Y-m-d');
        $endDate = $today->format('Y-m-d');

        Transaction::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $this->user1->id,
            'client_id' => $this->client->id,
            'reseau_id' => $this->reseau->id,
            'montant' => 10000,
            'type_operation' => 'depot',
            'statut' => 'ENREGISTREE',
            'consentement_recap' => 'Test consent',
            'consentement_methode' => 'confirmation_client',
            'consentement_confirme_le' => now(),
            'version' => 1,
            'sync_status' => 'SYNCED',
            'created_at' => $today->copy()->subDays(2),
        ]);

        $token = $this->user1->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->get('/api/v1/transactions/export?debut='.$startDate.'&fin='.$endDate);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        // Vérifier que le fichier a été créé
        $this->assertFileExists(storage_path('app/exports/releve-transactions_'.$startDate.'_'.$endDate.'.pdf'));

        // Vérifier le contenu du fichier
        $content = file_get_contents(storage_path('app/exports/releve-transactions_'.$startDate.'_'.$endDate.'.pdf'));
        $this->assertNotEmpty($content);
        $this->assertStringStartsWith('%PDF', $content);

        // Nettoyer
        unlink(storage_path('app/exports/releve-transactions_'.$startDate.'_'.$endDate.'.pdf'));
    }

    public function test_export_rejects_future_dates()
    {
        $token = $this->user1->createToken('test')->plainTextToken;

        $futureDate = now()->addDay()->format('Y-m-d');

        $response = $this->withToken($token)
            ->getJson("/api/v1/transactions/export?debut=2026-01-01&fin={$futureDate}");

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fin']);
    }

    public function test_export_rejects_period_longer_than_3_months()
    {
        $token = $this->user1->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/transactions/export?debut=2026-01-01&fin=2026-05-01');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fin']);
    }

    public function test_export_rejects_end_date_before_start_date()
    {
        $token = $this->user1->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/transactions/export?debut=2026-05-01&fin=2026-01-01');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['fin']);
    }

    public function test_export_agent_isolation()
    {
        $today = now();
        $startDate = $today->copy()->subDays(5)->format('Y-m-d');
        $endDate = $today->format('Y-m-d');

        Transaction::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $this->user1->id,
            'client_id' => $this->client->id,
            'reseau_id' => $this->reseau->id,
            'montant' => 10000,
            'type_operation' => 'depot',
            'statut' => 'ENREGISTREE',
            'consentement_recap' => 'Test consent',
            'consentement_methode' => 'confirmation_client',
            'consentement_confirme_le' => now(),
            'version' => 1,
            'sync_status' => 'SYNCED',
            'created_at' => $today->copy()->subDays(2),
        ]);

        $token = $this->user2->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->get('/api/v1/transactions/export?debut='.$startDate.'&fin='.$endDate);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Aucune transaction trouvée pour cette période.',
        ]);
    }

    public function test_export_rejects_missing_required_parameters()
    {
        $token = $this->user1->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/transactions/export');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['debut', 'fin']);
    }
}
