<?php

namespace Tests\Feature;

use App\Exceptions\ApiException;
use App\Http\Controllers\CaisseSessionController;
use App\Http\Controllers\MesReseauxController;
use App\Http\Resources\TransactionResource;
use App\Models\AgentReseau;
use App\Models\CaisseSession;
use App\Models\Client;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CaisseService;
use App\Services\SessionCaissePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class CaisseSessionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Reseau $reseau;

    private AgentReseau $agentReseau;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->reseau = Reseau::factory()->create([
            'owner_user_id' => $this->user->id,
            'nom' => 'Telecel Money',
            'code' => 'TM',
        ]);
        $this->agentReseau = AgentReseau::create([
            'user_id' => $this->user->id,
            'reseau_id' => $this->reseau->id,
            'actif' => true,
            'solde_float' => 0,
            'solde_cash' => 0,
        ]);
    }

    public function test_export_pdf_bilan_session_generates_document()
    {
        $session = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 80000,
            'solde_float_theorique' => 100000,
            'solde_cash_theorique' => 80000,
            'solde_float_reel' => 100000,
            'solde_cash_reel' => 80000,
            'ecart_float' => 0,
            'ecart_cash' => 0,
            'statut' => 'cloturee',
            'ouverte_at' => now()->subHour(),
            'cloturee_at' => now(),
        ]);

        $filepath = app(SessionCaissePdfService::class)->generateBilanSession($this->user, $session->id);

        try {
            $this->assertFileExists($filepath);
            $this->assertStringStartsWith('%PDF', file_get_contents($filepath));
        } finally {
            if (is_file($filepath)) {
                unlink($filepath);
            }
        }
    }

    public function test_cash_session_opening_log_contains_only_technical_ids()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Ouverture session caisse', Mockery::on(function (array $context): bool {
                return array_keys($context) === ['session_id', 'user_id']
                    && $context['user_id'] === $this->user->id
                    && CaisseSession::whereKey($context['session_id'])->exists();
            }));

        app(CaisseService::class)->ouvrirSession($this->agentReseau, 100000, 100000);
    }

    public function test_scenario_principal_sessions_et_transactions()
    {
        // S1 ouverte à 200000/850000 (première session, pas de comparaison)
        $session1 = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => 200000,
            'solde_cash_ouverture' => 850000,
            'statut' => 'ouverte',
            'ouverte_at' => now(),
        ]);

        // Mettre à jour le solde de l'agent
        $this->agentReseau->solde_float = 200000;
        $this->agentReseau->solde_cash = 850000;
        $this->agentReseau->save();

        // Créer un client
        $client = Client::create([
            'user_id' => $this->user->id,
            'telephone' => '70123456',
            'nom' => 'Test',
            'prenoms' => 'Client',
        ]);

        // Dépôt 10000
        $tx1 = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session1->id,
            'type_operation' => 'depot',
            'montant' => 10000,
            'reference' => 'TM001',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        // Appliquer effet sur solde
        $this->agentReseau->solde_float -= 10000;
        $this->agentReseau->solde_cash += 10000;
        $this->agentReseau->save();

        // Retrait 25000
        $tx2 = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session1->id,
            'type_operation' => 'retrait',
            'montant' => 25000,
            'reference' => 'TM002',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        // Appliquer effet sur solde
        $this->agentReseau->solde_float += 25000;
        $this->agentReseau->solde_cash -= 25000;
        $this->agentReseau->save();

        // Vérifier solde après transactions S1
        $this->agentReseau->refresh();
        $this->assertEquals(215000, $this->agentReseau->solde_float);
        $this->assertEquals(835000, $this->agentReseau->solde_cash);

        // Clôturer S1
        $session1->update([
            'statut' => 'cloturee',
            'cloturee_at' => now(),
            'solde_float_theorique' => 215000,
            'solde_cash_theorique' => 835000,
            'solde_float_reel' => 215000,
            'solde_cash_reel' => 835000,
            'ecart_float' => 0,
            'ecart_cash' => 0,
        ]);

        // Vérifier que les transactions restent rattachées à S1
        $tx1->refresh();
        $tx2->refresh();
        $this->assertEquals($session1->id, $tx1->caisse_session_id);
        $this->assertEquals($session1->id, $tx2->caisse_session_id);

        // S2 ouverte à 200000/200000 (écrasement, écart par rapport à S1)
        $service = app(CaisseService::class);
        $session2 = $service->ouvrirSession(
            $this->agentReseau,
            200000,
            200000,
            'Nouvelle déclaration de stock'
        );

        // Vérifier que les écarts d'ouverture sont enregistrés
        $session2->refresh();
        $this->assertEquals(-15000, $session2->ecart_float_ouverture);
        $this->assertEquals(-635000, $session2->ecart_cash_ouverture);
        $this->assertEquals('Nouvelle déclaration de stock', $session2->justification_ouverture);

        // Le solde a été écrasé à 200000/200000 par ouvrirSession
        $this->agentReseau->refresh();
        $this->assertEquals(200000, $this->agentReseau->solde_float);
        $this->assertEquals(200000, $this->agentReseau->solde_cash);

        // Dépôt 10000
        $tx3 = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session2->id,
            'type_operation' => 'depot',
            'montant' => 10000,
            'reference' => 'TM003',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        // Appliquer effet sur solde
        $this->agentReseau->solde_float -= 10000;
        $this->agentReseau->solde_cash += 10000;
        $this->agentReseau->save();

        // Dépôt 5000
        $tx4 = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session2->id,
            'type_operation' => 'depot',
            'montant' => 5000,
            'reference' => 'TM004',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        // Appliquer effet sur solde
        $this->agentReseau->solde_float -= 5000;
        $this->agentReseau->solde_cash += 5000;
        $this->agentReseau->save();

        // Vérifier : matérialisé = théorique = 185000/215000
        $this->agentReseau->refresh();
        $this->assertEquals(185000, $this->agentReseau->solde_float);
        $this->assertEquals(215000, $this->agentReseau->solde_cash);

        // Calculer théorique S2 via service
        $soldesTheoriques = $service->calculerSoldesTheoriques($session2);
        $this->assertEquals(185000, $soldesTheoriques['solde_float_theorique']);
        $this->assertEquals(215000, $soldesTheoriques['solde_cash_theorique']);
    }

    public function test_transaction_sans_session_ne_modifie_pas_soldes()
    {
        // Transaction sans caisse_session_id (historique pré-caisse)
        $client = Client::create([
            'user_id' => $this->user->id,
            'telephone' => '70987654',
            'nom' => 'Ancien',
            'prenoms' => 'Client',
        ]);

        $tx = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => null,
            'type_operation' => 'depot',
            'montant' => 5000,
            'reference' => 'OLD001',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        // Vérifier que les soldes ne sont pas modifiés
        $this->agentReseau->refresh();
        $this->assertEquals(0, $this->agentReseau->solde_float);
        $this->assertEquals(0, $this->agentReseau->solde_cash);
    }

    public function test_re_cloture_session_refusee()
    {
        $session = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'statut' => 'cloturee',
            'ouverte_at' => now()->subHour(),
            'cloturee_at' => now()->subMinutes(30),
        ]);

        $service = app(CaisseService::class);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Cette session est déjà clôturée');

        $service->cloturerSession($session, 100000, 100000, null);
    }

    public function test_caisse_session_id_non_modifiable()
    {
        $session = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'statut' => 'ouverte',
            'ouverte_at' => now(),
        ]);

        $client = Client::create([
            'user_id' => $this->user->id,
            'telephone' => '70111111',
            'nom' => 'Test',
            'prenoms' => 'Client',
        ]);

        $tx = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session->id,
            'type_operation' => 'depot',
            'montant' => 5000,
            'reference' => 'TM005',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        // Tenter de modifier caisse_session_id
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('caisse_session_id ne peut pas être modifié');

        $tx->caisse_session_id = 'autre-session-id';
        $tx->save();
    }

    public function test_transactions_annulee_non_comptee_dans_theorique()
    {
        $session = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'statut' => 'ouverte',
            'ouverte_at' => now(),
        ]);

        $client = Client::create([
            'user_id' => $this->user->id,
            'telephone' => '70222222',
            'nom' => 'Test',
            'prenoms' => 'Client',
        ]);

        // Transaction annulée
        $tx = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session->id,
            'type_operation' => 'depot',
            'montant' => 10000,
            'reference' => 'TM006',
            'statut' => 'ANNULEE',
            'version' => 1,
        ]);

        // Calculer théorique
        $service = app(CaisseService::class);
        $soldesTheoriques = $service->calculerSoldesTheoriques($session);

        // La transaction annulée ne doit pas être comptée
        $this->assertEquals(100000, $soldesTheoriques['solde_float_theorique']);
        $this->assertEquals(100000, $soldesTheoriques['solde_cash_theorique']);
    }

    public function test_ouverture_sans_ecart()
    {
        // Première session : ouverture sans écart (pas de session précédente)
        $service = app(CaisseService::class);
        $session = $service->ouvrirSession($this->agentReseau, 100000, 100000);

        $this->assertNull($session->ecart_float_ouverture);
        $this->assertNull($session->ecart_cash_ouverture);
        $this->assertNull($session->justification_ouverture);
    }

    public function test_ouverture_ecart_sans_justification_422()
    {
        // Créer une session précédente clôturée
        $session1 = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today()->subDay(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'solde_float_reel' => 100000,
            'solde_cash_reel' => 100000,
            'statut' => 'cloturee',
            'ouverte_at' => now()->subDay(),
            'cloturee_at' => now()->subDay()->addHour(),
        ]);

        // Tenter d'ouvrir avec un écart > 500 sans justification
        $service = app(CaisseService::class);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('La justification est obligatoire car l\'écart dépasse le seuil de 500');

        $service->ouvrirSession($this->agentReseau, 200000, 100000);
    }

    public function test_ouverture_ecart_avec_justification_200()
    {
        // Créer une session précédente clôturée
        $session1 = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today()->subDay(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'solde_float_reel' => 100000,
            'solde_cash_reel' => 100000,
            'statut' => 'cloturee',
            'ouverte_at' => now()->subDay(),
            'cloturee_at' => now()->subDay()->addHour(),
        ]);

        // Ouvrir avec un écart > 500 avec justification
        $service = app(CaisseService::class);
        $session2 = $service->ouvrirSession(
            $this->agentReseau,
            200000,
            100000,
            'Complément de stock apporté'
        );

        $session2->refresh();
        $this->assertEquals(100000, $session2->ecart_float_ouverture);
        $this->assertEquals(0, $session2->ecart_cash_ouverture);
        $this->assertEquals('Complément de stock apporté', $session2->justification_ouverture);
    }

    public function test_ouverture_ecart_inférieur_seuil_enregistre()
    {
        // Créer une session précédente clôturée
        $session1 = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today()->subDay(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'solde_float_reel' => 100000,
            'solde_cash_reel' => 100000,
            'statut' => 'cloturee',
            'ouverte_at' => now()->subDay(),
            'cloturee_at' => now()->subDay()->addHour(),
        ]);

        // Ouvrir avec un écart ≤ 500 sans justification
        $service = app(CaisseService::class);
        $session2 = $service->ouvrirSession($this->agentReseau, 100300, 100000);

        $session2->refresh();
        $this->assertEquals(300, $session2->ecart_float_ouverture);
        $this->assertEquals(0, $session2->ecart_cash_ouverture);
        $this->assertNull($session2->justification_ouverture);
    }

    public function test_premiere_ouverture_sans_comparaison()
    {
        // Utiliser un réseau différent pour créer un nouvel agent_reseau
        $newReseau = Reseau::create([
            'owner_user_id' => $this->user->id,
            'nom' => 'Orange Money',
            'code' => 'OM',
            'logo' => null,
        ]);

        $newAgent = AgentReseau::create([
            'user_id' => $this->user->id,
            'reseau_id' => $newReseau->id,
            'actif' => true,
            'solde_float' => 0,
            'solde_cash' => 0,
        ]);

        $service = app(CaisseService::class);
        $session = $service->ouvrirSession($newAgent, 50000, 50000);

        $this->assertNull($session->ecart_float_ouverture);
        $this->assertNull($session->ecart_cash_ouverture);
    }

    public function test_solde_suggere_egal_derniere_cloture()
    {
        // Créer une session clôturée
        $session1 = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today()->subDay(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'solde_float_reel' => 125000,
            'solde_cash_reel' => 75000,
            'statut' => 'cloturee',
            'ouverte_at' => now()->subDay(),
            'cloturee_at' => now()->subDay()->addHour(),
        ]);

        // Vérifier via MesReseauxController
        $controller = new MesReseauxController;
        $request = Request::create('/api/v1/reseaux', 'GET');
        $request->setUserResolver(fn () => $this->user);

        $response = $controller->index($request);
        $data = json_decode($response->getContent(), true)['data'];

        $reseauData = collect($data)->firstWhere('id', $this->reseau->id);
        $this->assertEquals(125000, $reseauData['solde_float_suggere']);
        $this->assertEquals(75000, $reseauData['solde_cash_suggere']);
    }

    public function test_solde_suggere_null_sans_cloture()
    {
        // Aucune session clôturée
        $controller = new MesReseauxController;
        $request = Request::create('/api/v1/reseaux', 'GET');
        $request->setUserResolver(fn () => $this->user);

        $response = $controller->index($request);
        $data = json_decode($response->getContent(), true)['data'];

        $reseauData = collect($data)->firstWhere('id', $this->reseau->id);
        $this->assertNull($reseauData['solde_float_suggere']);
        $this->assertNull($reseauData['solde_cash_suggere']);
    }

    public function test_bilan_expose_champs_ouverture()
    {
        $session = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'ecart_float_ouverture' => 5000,
            'ecart_cash_ouverture' => -3000,
            'justification_ouverture' => 'Petit écart de caisse',
            'statut' => 'ouverte',
            'ouverte_at' => now(),
        ]);

        $pdfService = $this->getMockBuilder(SessionCaissePdfService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = new CaisseSessionController(app(CaisseService::class), $pdfService);
        $request = Request::create('/api/v1/caisse/sessions/'.$session->id.'/bilan', 'GET');
        $request->setUserResolver(fn () => $this->user);

        $response = $controller->bilan($request, $session->id);
        $data = json_decode($response->getContent(), true)['data']['session'];

        $this->assertEquals(5000, $data['ecart_float_ouverture']);
        $this->assertEquals(-3000, $data['ecart_cash_ouverture']);
        $this->assertEquals('Petit écart de caisse', $data['justification_ouverture']);
    }

    public function test_transaction_modifiable_field()
    {
        // Créer un client
        $client = Client::create([
            'user_id' => $this->user->id,
            'telephone' => '70123456',
            'nom' => 'Test',
            'prenoms' => 'Client',
        ]);

        // Transaction sans session (modifiable)
        $tx1 = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => null,
            'type_operation' => 'depot',
            'montant' => 10000,
            'reference' => 'TM001',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        $resource = new TransactionResource($tx1);
        $this->assertTrue($resource->toArray(request())['modifiable']);

        // Transaction de session ouverte (modifiable)
        $session = CaisseSession::create([
            'agent_reseau_id' => $this->agentReseau->id,
            'date' => today(),
            'solde_float_ouverture' => 100000,
            'solde_cash_ouverture' => 100000,
            'statut' => 'ouverte',
            'ouverte_at' => now(),
        ]);

        $tx2 = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session->id,
            'type_operation' => 'depot',
            'montant' => 10000,
            'reference' => 'TM002',
            'statut' => 'ENREGISTREE',
            'version' => 1,
        ]);

        $tx2->load('caisseSession');
        $resource2 = new TransactionResource($tx2);
        $this->assertTrue($resource2->toArray(request())['modifiable']);

        // Transaction de session clôturée (non modifiable)
        $session->update([
            'statut' => 'cloturee',
            'cloturee_at' => now(),
        ]);

        $tx2->refresh();
        $tx2->load('caisseSession');
        $resource3 = new TransactionResource($tx2);
        $this->assertFalse($resource3->toArray(request())['modifiable']);

        // Transaction ANNULEE (non modifiable)
        $tx3 = Transaction::create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'reseau_id' => $this->reseau->id,
            'caisse_session_id' => $session->id,
            'type_operation' => 'depot',
            'montant' => 10000,
            'reference' => 'TM003',
            'statut' => 'ANNULEE',
            'version' => 1,
        ]);

        $tx3->load('caisseSession');
        $resource4 = new TransactionResource($tx3);
        $this->assertFalse($resource4->toArray(request())['modifiable']);
    }
}
