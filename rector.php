<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Console',
        __DIR__ . '/Lib',
        __DIR__ . '/Model',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php80: true);
