<?php

namespace App\Services;

use App\Models\Reseau;
use Illuminate\Database\Eloquent\Collection;

class ReseauService
{
    public function list(bool $withDeleted = false): Collection
    {
        $query = Reseau::query()->orderBy('nom');

        if ($withDeleted) {
            $query->withTrashed();
        }

        return $query->get();
    }

    public function create(array $data): Reseau
    {
        if (empty($data['code'] ?? null)) {
            $data['code'] = $this->generateCodeFromName($data['nom']);
        }

        return Reseau::create($data);
    }

    public function update(Reseau $reseau, array $data): Reseau
    {
        $reseau->update($data);

        return $reseau->refresh();
    }

    public function delete(Reseau $reseau): void
    {
        $reseau->delete();
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
}
