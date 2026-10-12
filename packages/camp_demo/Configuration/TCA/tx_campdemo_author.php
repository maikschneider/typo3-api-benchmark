<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'Author',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => ['disabled' => 'hidden'],
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/status/status-user-frontend.svg',
    ],
    'columns' => [
        'name' => [
            'label' => 'Name',
            'config' => ['type' => 'input', 'required' => true, 'max' => 255],
        ],
        'bio' => [
            'label' => 'Bio',
            'config' => ['type' => 'text', 'rows' => 4],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'name, bio, --div--;core.form.tabs:access, hidden'],
    ],
];
