<?php

return [
    'general' => [
        'table' => 'tx_bench_article',
        'resourceName' => 'articles',
        'resourceType' => 'Article',
    ],
    'columns' => [
        'author' => ['embed' => true],
        'tags' => ['embed' => true],
        'categories' => ['embed' => true],
    ],
    'order' => [
        'default' => ['uid' => 'asc'],
    ],
    'cache' => [
        'enabled' => true,
    ],
];
