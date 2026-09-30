<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustements_caisse', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('agent_reseau_id')->constrained('agent_reseaux')->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('montant_float', 12, 2)->default(0);
            $table->decimal('montant_cash', 12, 2)->default(0);
            $table->text('motif')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('agent_reseau_id');
        });

        DB::statement("ALTER TABLE ajustements_caisse ADD CONSTRAINT ajustements_caisse_type_check CHECK (type IN ('recharge', 'correction'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustements_caisse');
    }
};
