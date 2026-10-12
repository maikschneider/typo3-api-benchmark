<?php

declare(strict_types=1);

return [
    'frontend' => [
        'maikschneider/camp-demo/query-count' => [
            'target' => \MaikSchneider\CampDemo\Middleware\QueryCountMiddleware::class,
            'after' => ['typo3/cms-frontend/timetracker'],
            'before' => ['typo3/cms-core/normalized-params-attribute'],
        ],
    ],
];
