<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CountLegacyCnibImageFields extends Command
{
    protected $signature = 'clients:count-cnib-image-data';

    protected $description = 'Count non-empty legacy CNIB image fields without reading their values';

    public function handle(): int
    {
        if (! Schema::hasColumn('clients', 'cni_recto')
            || ! Schema::hasColumn('clients', 'cni_verso')) {
            $this->warn('Les colonnes historiques ne sont pas toutes présentes.');

            return self::SUCCESS;
        }

        $counts = DB::table('clients')
            ->selectRaw("COUNT(CASE WHEN cni_recto IS NOT NULL AND cni_recto <> '' THEN 1 END) AS recto_count")
            ->selectRaw("COUNT(CASE WHEN cni_verso IS NOT NULL AND cni_verso <> '' THEN 1 END) AS verso_count")
            ->first();

        $this->table(
            ['Champ historique', 'Lignes non vides'],
            [
                ['Recto', $counts->recto_count],
                ['Verso', $counts->verso_count],
            ],
        );

        return self::SUCCESS;
    }
}
