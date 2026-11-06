<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\FormRequest;

arch('input DTOs are Spatie Data classes, not Form Requests')
    ->expect('App')
    ->not->toExtend(FormRequest::class);

it('keeps migrations forward-only', function (): void {
    $files = glob(dirname(__DIR__, 2) . '/database/migrations/*.php') ?: [];
    $withDown = array_values(array_filter(
        $files,
        static fn(string $file): bool => preg_match('/function\s+down\s*\(/', (string) file_get_contents($file)) === 1,
    ));

    expect($withDown)->toBeEmpty();
});
