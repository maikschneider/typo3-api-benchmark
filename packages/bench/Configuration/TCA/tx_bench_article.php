<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'Article',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'default_sortby' => 'published DESC',
        'enablecolumns' => ['disabled' => 'hidden'],
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/content/content-news.svg',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => ['type' => 'input', 'required' => true, 'max' => 255],
        ],
        'teaser' => [
            'label' => 'Teaser',
            'config' => ['type' => 'text', 'rows' => 3],
        ],
        'published' => [
            'label' => 'Published',
            'config' => ['type' => 'datetime', 'format' => 'date'],
        ],
        'reading_time' => [
            'label' => 'Reading time (minutes)',
            'config' => ['type' => 'number', 'range' => ['lower' => 1, 'upper' => 60]],
        ],
        'author' => [
            'label' => 'Author',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_bench_author',
                'items' => [['label' => '', 'value' => 0]],
                'default' => 0,
            ],
        ],
        'tags' => [
            'label' => 'Tags',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_bench_tag',
                'MM' => 'tx_bench_article_tag_mm',
            ],
        ],
        'categories' => [
            'label' => 'Categories',
            'config' => ['type' => 'category'],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, teaser, published, reading_time, author, tags, --div--;core.form.tabs:categories, categories, --div--;core.form.tabs:access, hidden'],
    ],
];
