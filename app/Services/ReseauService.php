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
}
