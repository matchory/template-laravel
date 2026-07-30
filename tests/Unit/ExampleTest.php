<?php

declare(strict_types=1);

it('runs unit tests without the framework', function (): void {
    expect(array_sum([1, 2, 3]))->toBe(6);
});
