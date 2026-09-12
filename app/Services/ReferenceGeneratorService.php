<?php

namespace App\Services;

use App\Models\Reseau;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReferenceGeneratorService
{
    public function generate(Reseau $reseau, ?Carbon $date = null): string
    {
        $date ??= now();
        $referenceDate = $date->toDateString();

        DB::table('transaction_reference_sequences')->insertOrIgnore([
            'id' => (string) str()->uuid(),
            'reseau_id' => $reseau->getKey(),
            'reference_date' => $referenceDate,
            'last_sequence' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = DB::table('transaction_reference_sequences')
            ->where('reseau_id', $reseau->getKey())
            ->where('reference_date', $referenceDate)
            ->lockForUpdate()
            ->first();

        $nextSequence = $sequence->last_sequence + 1;

        DB::table('transaction_reference_sequences')
            ->where('id', $sequence->id)
            ->update([
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        return sprintf(
            '%s-%s-%04d',
            $reseau->code,
            $date->format('ymd'),
            $nextSequence,
        );
    }
}
