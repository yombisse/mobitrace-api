<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignUuid('reseau_id')->constrained('reseaux')->restrictOnDelete();
            $table->string('type_operation', 10);
            $table->decimal('montant', 12, 2);
            $table->string('reference', 100)->nullable();
            $table->decimal('solde_apres_operation', 12, 2)->nullable();
            $table->text('note')->nullable();
            $table->string('statut', 20)->default('ENREGISTREE');
            $table->string('sync_status', 20)->default('SYNCED');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('client_id');
            $table->index('reseau_id');
            $table->index('created_at');
        });

        DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_type_operation_check CHECK (type_operation IN ('depot', 'retrait'))");
        DB::statement('ALTER TABLE transactions ADD CONSTRAINT transactions_montant_check CHECK (montant > 0)');
        DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_statut_check CHECK (statut IN ('ENREGISTREE', 'MODIFIEE', 'ANNULEE'))");
        DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_sync_status_check CHECK (sync_status IN ('SYNCED', 'PENDING', 'FAILED'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};