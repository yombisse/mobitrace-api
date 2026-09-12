<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_reference_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reseau_id')->constrained('reseaux')->restrictOnDelete();
            $table->date('reference_date');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();

            $table->unique(['reseau_id', 'reference_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_reference_sequences');
    }
};
