<?php

declare(strict_types=1);

use SGalinski\SgApiCore\Service\ApiRegistry;
use SGalinski\SgApiCore\Service\ResourceRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

GeneralUtility::makeInstance(ApiRegistry::class)->registerApi(
    'bench',
    ['1'],
    ['authMode' => 'public'],
    null,
    ['rateLimit' => ['enabled' => false]],
);

GeneralUtility::makeInstance(ResourceRegistry::class)->registerResource(
    'bench',
    'tx_bench_article',
    '/articles',
    [
        'allowedOperations' => ['list', 'get'],
        'readFields' => ['uid', 'title', 'teaser', 'published', 'reading_time', 'author', 'tags'],
        'fieldConfiguration' => [
            'tx_bench_author' => ['allowed' => ['uid', 'name']],
            'tx_bench_tag' => ['allowed' => ['uid', 'title']],
        ],
    ],
);

require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('bench_sgapicore') . 'Configuration/DepthResources.php';
