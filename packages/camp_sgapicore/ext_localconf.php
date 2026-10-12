<?php

declare(strict_types=1);

use SGalinski\SgApiCore\Service\ApiRegistry;
use SGalinski\SgApiCore\Service\ResourceRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

GeneralUtility::makeInstance(ApiRegistry::class)->registerApi(
    'camp',
    ['1'],
    ['authMode' => 'public'],
    null,
    ['rateLimit' => ['enabled' => false]],
);

GeneralUtility::makeInstance(ResourceRegistry::class)->registerResource(
    'camp',
    'tx_campdemo_article',
    '/articles',
    [
        'allowedOperations' => ['list', 'get'],
        'readFields' => ['uid', 'title', 'teaser', 'published', 'reading_time', 'author', 'tags'],
        'fieldConfiguration' => [
            'tx_campdemo_author' => ['allowed' => ['uid', 'name']],
            'tx_campdemo_tag' => ['allowed' => ['uid', 'title']],
        ],
    ],
);
