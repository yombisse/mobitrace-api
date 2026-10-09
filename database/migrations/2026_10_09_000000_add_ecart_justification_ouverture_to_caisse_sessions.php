<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->decimal('ecart_float_ouverture', 15, 2)->nullable()->after('solde_cash_ouverture');
            $table->decimal('ecart_cash_ouverture', 15, 2)->nullable()->after('ecart_float_ouverture');
            $table->text('justification_ouverture')->nullable()->after('ecart_cash_ouverture');
        });
    }

    public function down(): void
    {
        Schema::table('caisse_sessions', function (Blueprint $table) {
            $table->dropColumn(['ecart_float_ouverture', 'ecart_cash_ouverture', 'justification_ouverture']);
        });
    }
};
