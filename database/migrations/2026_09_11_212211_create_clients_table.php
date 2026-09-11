<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('telephone', 20);
            $table->string('nom', 100);
            $table->string('prenoms', 150);
            $table->date('date_naissance')->nullable();
            $table->string('nationalite', 100)->nullable();
            $table->string('type_piece', 50)->nullable();
            $table->string('numero_piece', 50)->nullable();
            $table->date('date_expiration_piece')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'telephone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};