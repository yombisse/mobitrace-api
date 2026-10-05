<?php

namespace App\Console\Commands;

use App\Models\AgentReseau;
use App\Models\CaisseSession;
use App\Models\Transaction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:link-transactions-to-sessions')]
#[Description('Lier rétroactivement les transactions sans session à leur session correspondante')]
class LinkTransactionsToSessions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recherche des transactions sans session...');

        // Trouver toutes les transactions sans caisse_session_id
        $transactionsWithoutSession = Transaction::whereNull('caisse_session_id')
            ->orderBy('created_at', 'asc')
            ->get();

        $count = $transactionsWithoutSession->count();

        if ($count === 0) {
            $this->info('Aucune transaction sans session trouvée.');
            return Command::SUCCESS;
        }

        $this->info("{$count} transactions sans session trouvées.");
        $this->newLine();

        $linked = 0;
        $notLinked = 0;

        foreach ($transactionsWithoutSession as $transaction) {
            // Trouver l'agent_reseau correspondant
            $agentReseau = AgentReseau::where('user_id', $transaction->user_id)
                ->where('reseau_id', $transaction->reseau_id)
                ->first();

            if (!$agentReseau) {
                $this->warn("Transaction {$transaction->id} : Aucun agent_reseau trouvé");
                $notLinked++;
                continue;
            }

            // Trouver la session correspondante
            // La session doit être pour le même agent_reseau
            // Et la transaction doit avoir été créée pendant la session (ouverte_at <= created_at <= cloturee_at)
            $session = CaisseSession::where('agent_reseau_id', $agentReseau->id)
                ->where('date', $transaction->created_at->toDateString())
                ->where('ouverte_at', '<=', $transaction->created_at)
                ->where(function ($query) use ($transaction) {
                    $query->whereNull('cloturee_at')
                        ->orWhere('cloturee_at', '>=', $transaction->created_at);
                })
                ->orderBy('ouverte_at', 'desc')
                ->first();

            if (!$session) {
                $this->warn("Transaction {$transaction->id} ({$transaction->created_at->format('Y-m-d H:i:s')}) : Aucune session correspondante trouvée");
                $notLinked++;
                continue;
            }

            // Lier la transaction à la session
            $transaction->caisse_session_id = $session->id;
            $transaction->save();

            $this->info("Transaction {$transaction->id} liée à la session {$session->id}");
            $linked++;
        }

        $this->newLine();
        $this->info('--- Résumé ---');
        $this->info("Transactions liées : {$linked}");
        $this->info("Transactions non liées : {$notLinked}");
        $this->info("Total : {$count}");

        return Command::SUCCESS;
    }
}
