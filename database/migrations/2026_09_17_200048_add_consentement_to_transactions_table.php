<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('consentement_recap')->nullable();
            $table->string('consentement_methode', 50)->default('confirmation_client');
            $table->timestamp('consentement_confirme_le')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['consentement_recap', 'consentement_methode', 'consentement_confirme_le']);
        });
    }
};
