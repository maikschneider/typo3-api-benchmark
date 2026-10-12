<?php

declare(strict_types=1);

return [
    'frontend' => [
        'maikschneider/camp-recordapi/api' => [
            'target' => \MaikSchneider\CampRecordapi\ApiMiddleware::class,
            'after' => ['typo3/cms-frontend/site'],
            'before' => ['typo3/cms-frontend/page-resolver'],
        ],
    ],
];
