<?php

return [
    'general' => [
        'table' => 'sys_category',
        'resourceName' => 'categories',
        'resourceType' => 'Category',
    ],
    'columns' => [
        'title' => ['groups' => ['list', 'show']],
        'items' => ['groups' => ['show']],
    ],
];
