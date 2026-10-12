<?php

return [
    'general' => [
        'table' => 'tx_bench_article',
        'resourceName' => 'articles-depth-0',
        'resourceType' => 'Article',
    ],
    'columns' => [
        'title' => ['groups' => ['list', 'show']],
        'teaser' => ['groups' => ['list', 'show']],
        'published' => ['groups' => ['list', 'show']],
        'reading_time' => ['groups' => ['list', 'show']],
    ],
    'order' => [
        'default' => ['uid' => 'asc'],
    ],
    'cache' => [
        'enabled' => true,
    ],
];
