<?php

return [
    'phones' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('LEGACY_DEMO_PHONES', '')),
    ))),
    'emails' => array_values(array_filter(array_map(
        static fn (string $email): string => mb_strtolower(trim($email)),
        explode(',', (string) env('LEGACY_DEMO_EMAILS', '')),
    ))),
];
