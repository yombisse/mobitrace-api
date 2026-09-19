<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->otherUser = User::factory()->create();
    }

    public function test_liste_transactions_sans_filtre()
    {
        Transaction::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'type_operation',
                        'montant',
                        'reference',
                        'statut',
                        'created_at',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                    'summary' => [
                        'count',
                        'total',
                    ]
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_filtre_par_periode_personnalisee()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(5),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(15),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?date_debut='.now()->subDays(10)->toDateString().'&date_fin='.now()->toDateString());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_periode_predefinie_aujourdhui()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?periode=aujourdhui');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_periode_predefinie_7jours()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(5),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(10),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?periode=7jours');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_periode_predefinie_cemois()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->startOfMonth()->addDays(5),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subMonth(),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?periode=cemois');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_reseau_par_id()
    {
        $reseau1 = Reseau::factory()->create(['code' => 'OM']);
        $reseau2 = Reseau::factory()->create(['code' => 'MV']);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'reseau_id' => $reseau1->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'reseau_id' => $reseau2->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?reseau_id='.$reseau1->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_reseau_par_code()
    {
        $reseau1 = Reseau::factory()->create(['code' => 'OM']);
        $reseau2 = Reseau::factory()->create(['code' => 'MV']);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'reseau_id' => $reseau1->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'reseau_id' => $reseau2->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?reseau_code=OM');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_statut()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'statut' => 'ENREGISTREE',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'statut' => 'ANNULEE',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?statut=ENREGISTREE');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_tranche_de_montant()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 5000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 15000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 25000,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?montant_min=10000&montant_max=20000');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_tri_par_montant_ascendant()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 30000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 10000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 20000,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_by=montant&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(10000.0, $data[0]['montant']);
        $this->assertEquals(20000.0, $data[1]['montant']);
        $this->assertEquals(30000.0, $data[2]['montant']);
    }

    public function test_tri_par_montant_descendant()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 10000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 30000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 20000,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_by=montant&sort_order=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(30000.0, $data[0]['montant']);
        $this->assertEquals(20000.0, $data[1]['montant']);
        $this->assertEquals(10000.0, $data[2]['montant']);
    }

    public function test_tri_par_date_par_defaut_descendant()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(3),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(1),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThan($data[1]['created_at'], $data[0]['created_at']);
        $this->assertGreaterThan($data[2]['created_at'], $data[1]['created_at']);
    }

    public function test_sort_by_invalide_rejete()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_by=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_by']);
    }

    public function test_sort_by_date_marche()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(2),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_by=date&sort_order=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThan($data[1]['created_at'], $data[0]['created_at']);
    }

    public function test_sort_by_amount_marche()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 10000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 30000,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_by=amount&sort_order=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(30000.0, $data[0]['montant']);
        $this->assertEquals(10000.0, $data[1]['montant']);
    }

    public function test_sort_order_invalide_rejete()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_order=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_order']);
    }

    public function test_periode_invalide_rejetee()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?periode=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['periode']);
    }

    public function test_date_future_rejetee()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?date_debut='.now()->addDay()->toDateString());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_debut']);
    }

    public function test_date_fin_avant_date_debut_rejetee()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?date_debut=2026-09-20&date_fin=2026-09-10');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_fin']);
    }

    public function test_periode_superieure_3_mois_rejetee()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?date_debut=2026-01-01&date_fin=2026-05-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_fin']);
    }

    public function test_combinaison_filtres_et_type_operation()
    {
        $reseau = Reseau::factory()->create(['code' => 'OM']);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'reseau_id' => $reseau->id,
            'type_operation' => 'depot',
            'montant' => 15000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'reseau_id' => $reseau->id,
            'type_operation' => 'retrait',
            'montant' => 15000,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'reseau_id' => $reseau->id,
            'type_operation' => 'depot',
            'montant' => 5000,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?reseau_id='.$reseau->id.'&type_operation=depot&montant_min=10000');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_isolation_entre_agents()
    {
        Transaction::factory()->count(3)->create(['user_id' => $this->user->id]);
        Transaction::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_recherche_inclut_telephone()
    {
        $client = Client::factory()->create([
            'user_id' => $this->user->id,
            'telephone' => '70123456',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $otherClient = Client::factory()->create([
            'user_id' => $this->user->id,
            'telephone' => '80987654',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $otherClient->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?search=701');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_pagination_stable_avec_tri()
    {
        // Créer des transactions avec des montants identiques pour tester la stabilité
        Transaction::factory()->count(25)->create([
            'user_id' => $this->user->id,
            'montant' => 10000,
        ]);

        $page1 = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_by=montant&sort_order=asc&per_page=10');

        $page2 = $this->withToken($this->token)
            ->getJson('/api/v1/transactions?sort_by=montant&sort_order=asc&per_page=10&page=2');

        $page1Ids = collect($page1->json('data'))->pluck('id');
        $page2Ids = collect($page2->json('data'))->pluck('id');

        // Vérifier qu'il n'y a pas de doublon entre les pages
        $this->assertEquals(0, $page1Ids->intersect($page2Ids)->count());
    }

    public function test_acces_non_authentifie_refuse()
    {
        $response = $this->getJson('/api/v1/transactions');

        $response->assertStatus(401);
    }

    public function test_resume_exclut_transactions_annulees()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 10000,
            'statut' => 'ENREGISTREE',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 5000,
            'statut' => 'ANNULEE',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'montant' => 15000,
            'statut' => 'ENREGISTREE',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200);

        $summary = $response->json('meta.summary');
        $this->assertEquals(2, $summary['count']); // Seulement 2 transactions non annulées
        $this->assertEquals(25000.0, $summary['total']); // 10000 + 15000, pas 5000
    }
}
