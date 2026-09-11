<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 20);
            $table->jsonb('anciennes_donnees')->nullable();
            $table->jsonb('nouvelles_donnees')->nullable();
            $table->text('motif')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('transaction_id');
        });

        DB::statement("ALTER TABLE transaction_histories ADD CONSTRAINT transaction_histories_action_check CHECK (action IN ('CREATION', 'MODIFICATION', 'ANNULATION'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_histories');
    }
};