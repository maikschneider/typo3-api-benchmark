<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'Tag',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => ['disabled' => 'hidden'],
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/actions/actions-tag.svg',
    ],
    'columns' => [
        'title' => [
            'label' => 'Title',
            'config' => ['type' => 'input', 'required' => true, 'max' => 255],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'title, --div--;core.form.tabs:access, hidden'],
    ],
];
