<?php

declare(strict_types=1);

use Matchory\CodingStyle\Rector\Preset;
use Rector\Config\RectorConfig;

return Preset::laravel(RectorConfig::configure())
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withCache(cacheDirectory: __DIR__ . '/.cache/rector');
