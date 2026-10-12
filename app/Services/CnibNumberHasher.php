<?php

namespace App\Services;

use InvalidArgumentException;
use LogicException;

class CnibNumberHasher
{
    public function assertConfigured(): void
    {
        $key = config('cnib.hash_key');

        if (! is_string($key) || strlen($key) < 32) {
            throw new LogicException('CNIB_HASH_KEY doit contenir au moins 32 octets.');
        }
    }

    public function hash(string $numeroPiece): string
    {
        $this->assertConfigured();

        $normalized = preg_replace('/\s+/u', '', $numeroPiece);
        if ($normalized === null) {
            throw new InvalidArgumentException('Le numéro de pièce ne peut pas être normalisé.');
        }

        return hash_hmac(
            'sha256',
            mb_strtoupper($normalized, 'UTF-8'),
            config('cnib.hash_key'),
        );
    }
}
