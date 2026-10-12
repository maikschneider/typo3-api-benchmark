<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'Organization',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => ['disabled' => 'hidden'],
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/apps/apps-pagetree-folder-default.svg',
    ],
    'columns' => [
        'name' => [
            'label' => 'Name',
            'config' => ['type' => 'input', 'required' => true, 'max' => 255],
        ],
        'country' => [
            'label' => 'Country',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_bench_country',
                'items' => [['label' => '', 'value' => 0]],
                'default' => 0,
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'name, country, --div--;core.form.tabs:access, hidden'],
    ],
];
