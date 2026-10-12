<?php

namespace App\Console\Commands;

use App\Services\CnibNumberHasher;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EncryptClientDocumentNumbers extends Command
{
    protected $signature = 'cnib:encrypt-numbers {--dry-run : Count rows that would be changed without writing}';

    protected $description = 'Encrypt existing client document numbers and backfill their blind indexes';

    public function handle(CnibNumberHasher $hasher): int
    {
        $hasher->assertConfigured();

        $dryRun = (bool) $this->option('dry-run');
        $processed = 0;

        DB::table('clients')
            ->select(['id', 'numero_piece', 'numero_piece_hash'])
            ->orderBy('id')
            ->chunkById(500, function ($clients) use ($dryRun, $hasher, &$processed): void {
                foreach ($clients as $client) {
                    $storedValue = $client->numero_piece;
                    if ($storedValue === null) {
                        continue;
                    }

                    try {
                        $plainValue = Crypt::decryptString($storedValue);
                        $alreadyEncrypted = true;
                    } catch (DecryptException $exception) {
                        if ($this->looksLikeEncryptedValue($storedValue)) {
                            throw new RuntimeException('Un numéro semble chiffré mais ne peut pas être déchiffré avec APP_KEY.', previous: $exception);
                        }

                        $plainValue = $storedValue;
                        $alreadyEncrypted = false;
                    }

                    $expectedHash = $hasher->hash($plainValue);
                    $hashIsCurrent = is_string($client->numero_piece_hash)
                        && hash_equals($expectedHash, $client->numero_piece_hash);

                    if ($alreadyEncrypted && $hashIsCurrent) {
                        continue;
                    }

                    $processed++;

                    if (! $dryRun) {
                        DB::table('clients')
                            ->where('id', $client->id)
                            ->update([
                                'numero_piece' => $alreadyEncrypted
                                    ? $storedValue
                                    : Crypt::encryptString($plainValue),
                                'numero_piece_hash' => $expectedHash,
                            ]);
                    }
                }
            });

        $label = $dryRun ? 'Lignes à traiter' : 'Lignes traitées';
        $this->info("{$label} : {$processed}");

        return self::SUCCESS;
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
}
