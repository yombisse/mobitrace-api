<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseaux', function (Blueprint $table): void {
            $table->foreignUuid('owner_user_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
            $table->dropUnique('reseaux_code_unique');
        });

        DB::transaction(function (): void {
            $reseaux = DB::table('reseaux')->orderBy('id')->get();

            foreach ($reseaux as $reseau) {
                $ownerIds = DB::table('agent_reseaux')
                    ->where('reseau_id', $reseau->id)
                    ->pluck('user_id')
                    ->merge(
                        DB::table('transactions')
                            ->where('reseau_id', $reseau->id)
                            ->pluck('user_id')
                    )
                    ->unique()
                    ->values();

                foreach ($ownerIds as $index => $ownerId) {
                    $ownedReseauId = $reseau->id;

                    if ($index === 0) {
                        DB::table('reseaux')
                            ->where('id', $reseau->id)
                            ->update(['owner_user_id' => $ownerId]);
                    } else {
                        $ownedReseauId = (string) str()->uuid();
                        DB::table('reseaux')->insert([
                            'id' => $ownedReseauId,
                            'owner_user_id' => $ownerId,
                            'nom' => $reseau->nom,
                            'code' => $reseau->code,
                            'logo' => $reseau->logo,
                            'deleted_at' => $reseau->deleted_at,
                        ]);

                        $sequences = DB::table('transaction_reference_sequences')
                            ->where('reseau_id', $reseau->id)
                            ->get();

                        foreach ($sequences as $sequence) {
                            DB::table('transaction_reference_sequences')->insert([
                                'id' => (string) str()->uuid(),
                                'reseau_id' => $ownedReseauId,
                                'reference_date' => $sequence->reference_date,
                                'last_sequence' => $sequence->last_sequence,
                                'created_at' => $sequence->created_at,
                                'updated_at' => $sequence->updated_at,
                            ]);
                        }
                    }

                    DB::table('transactions')
                        ->where('reseau_id', $reseau->id)
                        ->where('user_id', $ownerId)
                        ->update(['reseau_id' => $ownedReseauId]);
                    DB::table('agent_reseaux')
                        ->where('reseau_id', $reseau->id)
                        ->where('user_id', $ownerId)
                        ->update(['reseau_id' => $ownedReseauId]);
                }
            }
        });

        Schema::table('reseaux', function (Blueprint $table): void {
            $table->unique(['owner_user_id', 'code']);
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $duplicateCodes = DB::table('reseaux')
                ->select('code')
                ->groupBy('code')
                ->havingRaw('count(*) > 1')
                ->pluck('code');

            foreach ($duplicateCodes as $code) {
                $reseauIds = DB::table('reseaux')
                    ->where('code', $code)
                    ->orderBy('id')
                    ->pluck('id');
                $canonicalId = $reseauIds->shift();

                foreach ($reseauIds as $reseauId) {
                    $sequences = DB::table('transaction_reference_sequences')
                        ->where('reseau_id', $reseauId)
                        ->get();

                    foreach ($sequences as $sequence) {
                        $canonicalSequence = DB::table('transaction_reference_sequences')
                            ->where('reseau_id', $canonicalId)
                            ->where('reference_date', $sequence->reference_date)
                            ->first();

                        if ($canonicalSequence === null) {
                            DB::table('transaction_reference_sequences')
                                ->where('id', $sequence->id)
                                ->update(['reseau_id' => $canonicalId]);
                        } else {
                            DB::table('transaction_reference_sequences')
                                ->where('id', $canonicalSequence->id)
                                ->update([
                                    'last_sequence' => max(
                                        $canonicalSequence->last_sequence,
                                        $sequence->last_sequence,
                                    ),
                                ]);
                            DB::table('transaction_reference_sequences')
                                ->where('id', $sequence->id)
                                ->delete();
                        }
                    }

                    DB::table('transactions')
                        ->where('reseau_id', $reseauId)
                        ->update(['reseau_id' => $canonicalId]);
                    DB::table('agent_reseaux')
                        ->where('reseau_id', $reseauId)
                        ->update(['reseau_id' => $canonicalId]);
                    DB::table('reseaux')->where('id', $reseauId)->delete();
                }
            }
        });

        Schema::table('reseaux', function (Blueprint $table): void {
            $table->dropUnique(['owner_user_id', 'code']);
            $table->dropConstrainedForeignId('owner_user_id');
            $table->unique('code');
        });
    }
};
