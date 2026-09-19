<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer les transactions de test en EN_ATTENTE
        DB::statement("DELETE FROM transactions WHERE statut = 'EN_ATTENTE'");

        // Mettre à jour la contrainte CHECK pour retirer EN_ATTENTE
        DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_statut_check");
        DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_statut_check CHECK (statut IN ('ENREGISTREE', 'MODIFIEE', 'ANNULEE'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer la contrainte CHECK avec EN_ATTENTE
        DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_statut_check");
        DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_statut_check CHECK (statut IN ('EN_ATTENTE', 'ENREGISTREE', 'MODIFIEE', 'ANNULEE'))");
    }
};
