<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LegacyDemoAccountSelector
{
    public function isConfigured(): bool
    {
        return $this->phones() !== [] || $this->emails() !== [];
    }

    /**
     * @return Builder<User>
     */
    public function query(): Builder
    {
        $phones = $this->phones();
        $emails = $this->emails();

        return User::withTrashed()->where(function (Builder $query) use ($phones, $emails): void {
            if ($phones !== []) {
                $query->whereIn('telephone', $phones);
            }

            if ($emails !== []) {
                $method = $phones === [] ? 'whereIn' : 'orWhereIn';
                $query->{$method}('email', $emails);
            }
        });
    }

    /**
     * @return list<string>
     */
    private function phones(): array
    {
        return config('legacy_demo_accounts.phones', []);
    }

    /**
     * @return list<string>
     */
    private function emails(): array
    {
        return config('legacy_demo_accounts.emails', []);
    }
}
