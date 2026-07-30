<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__ . '/../routes/web.php')
    ->withMiddleware()
    ->withExceptions()
    ->create();
