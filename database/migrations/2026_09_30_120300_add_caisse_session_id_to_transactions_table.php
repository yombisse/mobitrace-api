<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('caisse_session_id')
                ->nullable()
                ->after('reseau_id')
                ->constrained('caisse_sessions')
                ->restrictOnDelete();

            $table->index('caisse_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('caisse_session_id');
        });
    }
};
