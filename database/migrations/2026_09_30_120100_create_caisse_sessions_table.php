<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisse_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agent_reseau_id')->constrained('agent_reseaux')->restrictOnDelete();
            $table->date('date');

            $table->decimal('solde_float_ouverture', 12, 2);
            $table->decimal('solde_cash_ouverture', 12, 2);

            $table->decimal('solde_float_theorique', 12, 2)->nullable();
            $table->decimal('solde_cash_theorique', 12, 2)->nullable();

            $table->decimal('solde_float_reel', 12, 2)->nullable();
            $table->decimal('solde_cash_reel', 12, 2)->nullable();

            $table->decimal('ecart_float', 12, 2)->nullable();
            $table->decimal('ecart_cash', 12, 2)->nullable();
            $table->text('justification')->nullable();

            $table->string('statut', 20)->default('ouverte');

            $table->timestamp('ouverte_at')->useCurrent();
            $table->timestamp('cloturee_at')->nullable();

            $table->unique(['agent_reseau_id', 'date']);
            $table->index('agent_reseau_id');
            $table->index('date');
        });

        DB::statement("ALTER TABLE caisse_sessions ADD CONSTRAINT caisse_sessions_statut_check CHECK (statut IN ('ouverte', 'cloturee'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_sessions');
    }
};
