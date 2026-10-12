<?php

declare(strict_types=1);

return [
    \MaikSchneider\CampDemo\Domain\Model\Article::class => ['tableName' => 'tx_campdemo_article'],
    \MaikSchneider\CampDemo\Domain\Model\Author::class => ['tableName' => 'tx_campdemo_author'],
    \MaikSchneider\CampDemo\Domain\Model\Tag::class => ['tableName' => 'tx_campdemo_tag'],
    \MaikSchneider\CampDemo\Domain\Model\Category::class => ['tableName' => 'sys_category'],
];
