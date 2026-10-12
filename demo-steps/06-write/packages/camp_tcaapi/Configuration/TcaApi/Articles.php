<?php

use MaikSchneider\TcaApi\Enum\AccessRole;
use MaikSchneider\TcaApi\Filter\ExactFilter;
use MaikSchneider\TcaApi\Filter\MmFilter;
use MaikSchneider\TcaApi\Filter\RangeFilter;
use MaikSchneider\TcaApi\Filter\SearchFilter;

return [
    'general' => [
        'table' => 'tx_campdemo_article',
        'resourceName' => 'articles',
        'resourceType' => 'Article',
        'operations' => ['list', 'show', 'create', 'update'],
        'storagePid' => 2,
        'readStoragePids' => '*',
    ],
    'filters' => [
        'q' => [SearchFilter::class, ['columns' => ['title', 'teaser']]],
        'author.name' => ExactFilter::class,
        'tags' => MmFilter::class,
        'categories.title' => ExactFilter::class,
        'reading_time' => RangeFilter::class,
    ],
    'columns' => [
        'author' => ['embed' => true],
        'tags' => ['embed' => true],
        'categories' => ['embed' => true],
    ],
    'order' => [
        'allowed' => ['title', 'published', 'reading_time'],
        'default' => ['published' => 'desc'],
    ],
    'cache' => [
        'enabled' => true,
    ],
    'security' => [
        'create' => AccessRole::BE_USER,
        'update' => AccessRole::BE_USER,
    ],
];
