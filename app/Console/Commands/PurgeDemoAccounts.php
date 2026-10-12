<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\LegacyDemoAccountSelector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeDemoAccounts extends Command
{
    protected $signature = 'accounts:purge-demo {--confirm : Permanently delete matching demo accounts}';

    protected $description = 'Permanently delete configured legacy demo accounts';

    public function handle(LegacyDemoAccountSelector $selector): int
    {
        if (! $selector->isConfigured()) {
            $this->error('Configurez les sélecteurs privés LEGACY_DEMO_PHONES ou LEGACY_DEMO_EMAILS avant cette commande.');

            return self::FAILURE;
        }

        if (! $this->option('confirm')) {
            $this->info('Comptes correspondants : '.$selector->query()->count());
            $this->warn('Aucune suppression effectuée. Utilisez --confirm après vérification.');

            return self::SUCCESS;
        }

        $deleted = DB::transaction(function () use ($selector): int {
            $ids = $selector->query()->lockForUpdate()->pluck('id');
            $this->info('Comptes concernés : '.$ids->count());

            return $ids->isEmpty()
                ? 0
                : User::withTrashed()->whereKey($ids)->forceDelete();
        });

        $this->info("Comptes supprimés : {$deleted}");

        return self::SUCCESS;
    }
}
