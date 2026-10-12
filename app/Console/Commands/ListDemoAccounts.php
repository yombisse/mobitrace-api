<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\LegacyDemoAccountSelector;
use Illuminate\Console\Command;

class ListDemoAccounts extends Command
{
    protected $signature = 'accounts:list-demo';

    protected $description = 'List legacy demo accounts without exposing contact details or passwords';

    public function handle(LegacyDemoAccountSelector $selector): int
    {
        if (! $selector->isConfigured()) {
            $this->error('Configurez les sélecteurs privés LEGACY_DEMO_PHONES ou LEGACY_DEMO_EMAILS avant cette commande.');

            return self::FAILURE;
        }

        $users = $selector->query()
            ->orderBy('id')
            ->get(['id', 'created_at', 'deleted_at']);

        $this->info('Comptes correspondants : '.$users->count());
        $this->table(
            ['ID', 'Créé le', 'Supprimé le'],
            $users->map(fn (User $user): array => [
                $user->getKey(),
                $user->created_at?->toIso8601String() ?? '',
                $user->deleted_at?->toIso8601String() ?? '',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
