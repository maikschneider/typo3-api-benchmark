<?php

declare(strict_types=1);

use SGalinski\SgApiCore\Service\ApiRegistry;
use SGalinski\SgApiCore\Service\ResourceRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// Articles with the relation chain author → organization → country. sg_apicore keeps one resource
// per table and API, so every depth is its own API: /_sgapi/bench-depth-{depth}/v1/articles.
$fields = ['uid', 'title', 'teaser', 'published', 'reading_time'];

foreach (range(0, 3) as $depth) {
    $apiId = 'bench-depth-' . $depth;
    GeneralUtility::makeInstance(ApiRegistry::class)->registerApi(
        $apiId,
        ['1'],
        ['authMode' => 'public'],
        null,
        ['rateLimit' => ['enabled' => false]],
    );
    GeneralUtility::makeInstance(ResourceRegistry::class)->registerResource($apiId, 'tx_bench_article', '/articles', [
        'allowedOperations' => ['list'],
        'readFields' => $depth > 0 ? [...$fields, 'author'] : $fields,
        'resolveDepth' => $depth,
        'fieldConfiguration' => [
            'tx_bench_author' => ['allowed' => $depth > 1 ? ['uid', 'name', 'organization'] : ['uid', 'name']],
            'tx_bench_organization' => ['allowed' => $depth > 2 ? ['uid', 'name', 'country'] : ['uid', 'name']],
            'tx_bench_country' => ['allowed' => ['uid', 'title']],
        ],
    ]);
}
