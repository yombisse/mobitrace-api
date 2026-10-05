<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->dropUnique(['agent_reseau_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->unique(['agent_reseau_id', 'date']);
        });
    }
};
