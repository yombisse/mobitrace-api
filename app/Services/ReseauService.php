<?php

namespace App\Services;

use App\Models\Reseau;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReseauService
{
    public function list(User $user, bool $withDeleted = false): Collection
    {
        $query = $user->reseaux()->orderBy('nom');

        if ($withDeleted) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function create(User $user, array $data): Reseau
    {
        $baseCode = $this->generateCodeFromName($data['nom']);
        $this->ensureCodeBaseIsAvailable($user, $baseCode);

        if (empty($data['code'] ?? null)) {
            $data['code'] = $baseCode;
        }

        return DB::transaction(function () use ($user, $data): Reseau {
            $reseau = $user->reseaux()->create($data);

            $reseau->agentReseaux()->create([
                'user_id' => $user->getKey(),
                'actif' => true,
                'solde_float' => 0,
                'solde_cash' => 0,
            ]);

            return $reseau;
        });
    }

    public function update(User $user, Reseau $reseau, array $data): Reseau
    {
        $this->ensureOwned($user, $reseau);
        $reseau->update($data);

        return $reseau->refresh();
    }

    public function delete(User $user, Reseau $reseau): void
    {
        $this->ensureOwned($user, $reseau);
        $reseau->delete();
    }

    private function ensureOwned(User $user, Reseau $reseau): void
    {
        if ($reseau->owner_user_id !== $user->getKey()) {
            abort(404);
        }
    }

    private function generateCodeFromName(string $name): string
    {
        $code = '';

        // Traiter les mots séparés par espace ou tiret
        $words = preg_split('/[\s\-]+/', $name);

        foreach ($words as $word) {
            // Extraire les lettres majuscules pour les mots composés (ex: LiguidiCash → LC)
            $uppercaseLetters = preg_replace('/[^A-Z]/', '', $word);

            if (strlen($uppercaseLetters) > 1) {
                // Si le mot contient plusieurs majuscules (CamelCase), les utiliser
                $code .= $uppercaseLetters;
            } else {
                // Sinon, prendre la première lettre
                $code .= strtoupper(substr($word, 0, 1));
            }
        }

        if (strlen($code) > 50) {
            $code = substr($code, 0, 50);
        }

        return $code;
    }

    private function ensureCodeBaseIsAvailable(User $user, string $baseCode): void
    {
        $existingCodes = $user->reseaux()
            ->withTrashed()
            ->where(function ($query) use ($baseCode): void {
                $query->where('code', $baseCode)
                    ->orWhere('code', 'like', $baseCode.'-%');
            })
            ->pluck('code');

        foreach ($existingCodes as $existingCode) {
            if (
                $existingCode === $baseCode
                || preg_match('/^'.preg_quote($baseCode, '/').'-\d+$/i', $existingCode) === 1
            ) {
                throw ValidationException::withMessages([
                    'nom' => ['Un réseau avec ce code de base existe déjà. Choisissez un autre nom.'],
                ]);
            }
        }
    }
}
