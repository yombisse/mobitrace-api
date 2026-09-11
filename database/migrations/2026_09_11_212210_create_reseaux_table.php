<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom', 100);
            $table->string('code', 50)->unique();
            $table->string('logo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseaux');
    }
};