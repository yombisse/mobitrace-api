<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_reseaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('reseau_id')->constrained('reseaux')->restrictOnDelete();
            $table->boolean('actif')->default(true);
            $table->decimal('solde_float', 12, 2)->default(0);
            $table->decimal('solde_cash', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'reseau_id']);
            $table->index('user_id');
            $table->index('reseau_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_reseaux');
    }
};
