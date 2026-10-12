<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->text('numero_piece')->nullable()->change();
            $table->string('numero_piece_hash', 64)->nullable()->index();
        });
    }

    public function down(): void
    {
        DB::table('clients')
            ->whereNotNull('numero_piece')
            ->select(['id', 'numero_piece'])
            ->orderBy('id')
            ->chunkById(500, function ($clients): void {
                foreach ($clients as $client) {
                    $storedValue = $client->numero_piece;

                    try {
                        $plainValue = Crypt::decryptString($storedValue);
                    } catch (DecryptException $exception) {
                        if ($this->looksLikeEncryptedValue($storedValue)) {
                            throw new RuntimeException('Un numéro chiffré ne peut pas être restauré avec APP_KEY.', previous: $exception);
                        }

                        $plainValue = $storedValue;
                    }

                    if (mb_strlen($plainValue, 'UTF-8') > 50) {
                        throw new RuntimeException('Un numéro dépasse la taille du schéma historique; restauration interrompue.');
                    }

                    DB::table('clients')
                        ->where('id', $client->id)
                        ->update(['numero_piece' => $plainValue]);
                }
            });

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex(['numero_piece_hash']);
            $table->dropColumn('numero_piece_hash');
            $table->string('numero_piece', 50)->nullable()->change();
        });
    }

    private function looksLikeEncryptedValue(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }
};
