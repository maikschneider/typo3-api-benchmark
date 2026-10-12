<?php

declare(strict_types=1);

return [
    'frontend' => [
        'maikschneider/camp-demo/query-count' => [
            'target' => \MaikSchneider\CampDemo\Middleware\QueryCountMiddleware::class,
            'after' => ['typo3/cms-frontend/site'],
            'before' => ['maikschneider/tca-api/middleware'],
        ],
        'maikschneider/camp-demo/benchmark' => [
            'target' => \MaikSchneider\CampDemo\Middleware\BenchmarkMiddleware::class,
            'after' => ['maikschneider/camp-demo/query-count'],
            'before' => ['maikschneider/tca-api/middleware'],
        ],
    ],
];
