<?php

declare(strict_types=1);

return [
    'frontend' => [
        'typo3-api-benchmark/recordapi/api' => [
            'target' => \Typo3ApiBenchmark\Recordapi\ApiMiddleware::class,
            'after' => ['typo3/cms-frontend/site'],
            'before' => ['typo3/cms-frontend/page-resolver'],
        ],
        'typo3-api-benchmark/recordapi/depth' => [
            'target' => \Typo3ApiBenchmark\Recordapi\DepthApiMiddleware::class,
            'after' => ['typo3/cms-frontend/site'],
            'before' => ['typo3/cms-frontend/page-resolver'],
        ],
    ],
];
