<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ajustements_caisse', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->useCurrent()->after('created_at');
        });

        // Mettre à jour les enregistrements existants pour que updated_at = created_at
        DB::statement("UPDATE ajustements_caisse SET updated_at = created_at WHERE updated_at IS NULL");

        // Rendre la colonne non-nullable après la mise à jour
        Schema::table('ajustements_caisse', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ajustements_caisse', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
