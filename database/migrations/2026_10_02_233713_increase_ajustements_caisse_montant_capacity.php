<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ajustements_caisse', function (Blueprint $table) {
            $table->decimal('montant_float', 18, 2)->change();
            $table->decimal('montant_cash', 18, 2)->change();
        });

        Schema::table('agent_reseaux', function (Blueprint $table) {
            $table->decimal('solde_float', 18, 2)->change();
            $table->decimal('solde_cash', 18, 2)->change();
        });

        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->decimal('solde_float_ouverture', 18, 2)->change();
            $table->decimal('solde_cash_ouverture', 18, 2)->change();
            $table->decimal('solde_float_theorique', 18, 2)->nullable()->change();
            $table->decimal('solde_cash_theorique', 18, 2)->nullable()->change();
            $table->decimal('solde_float_reel', 18, 2)->nullable()->change();
            $table->decimal('solde_cash_reel', 18, 2)->nullable()->change();
            $table->decimal('ecart_float', 18, 2)->nullable()->change();
            $table->decimal('ecart_cash', 18, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ajustements_caisse', function (Blueprint $table) {
            $table->decimal('montant_float', 12, 2)->change();
            $table->decimal('montant_cash', 12, 2)->change();
        });

        Schema::table('agent_reseaux', function (Blueprint $table) {
            $table->decimal('solde_float', 12, 2)->change();
            $table->decimal('solde_cash', 12, 2)->change();
        });

        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->decimal('solde_float_ouverture', 12, 2)->change();
            $table->decimal('solde_cash_ouverture', 12, 2)->change();
            $table->decimal('solde_float_theorique', 12, 2)->nullable()->change();
            $table->decimal('solde_cash_theorique', 12, 2)->nullable()->change();
            $table->decimal('solde_float_reel', 12, 2)->nullable()->change();
            $table->decimal('solde_cash_reel', 12, 2)->nullable()->change();
            $table->decimal('ecart_float', 12, 2)->nullable()->change();
            $table->decimal('ecart_cash', 12, 2)->nullable()->change();
        });
    }
};
