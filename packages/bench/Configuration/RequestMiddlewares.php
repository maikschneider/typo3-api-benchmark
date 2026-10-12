<?php

declare(strict_types=1);

return [
    'frontend' => [
        'typo3-api-benchmark/bench/query-count' => [
            'target' => \Typo3ApiBenchmark\Bench\Middleware\QueryCountMiddleware::class,
            'after' => ['typo3/cms-frontend/timetracker'],
            'before' => ['typo3/cms-core/normalized-params-attribute'],
        ],
    ],
];
