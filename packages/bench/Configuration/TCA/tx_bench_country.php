<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'Country',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => ['disabled' => 'hidden'],
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/apps/apps-pagetree-folder-default.svg',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => ['type' => 'input', 'required' => true, 'max' => 255],
        ],
        'code' => [
            'label' => 'ISO code',
            'config' => ['type' => 'input', 'max' => 2],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, code, --div--;core.form.tabs:access, hidden'],
    ],
];
