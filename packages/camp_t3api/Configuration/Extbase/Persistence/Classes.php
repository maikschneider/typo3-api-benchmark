<?php

declare(strict_types=1);

return [
    \MaikSchneider\CampT3api\Domain\Model\Article::class => ['tableName' => 'tx_campdemo_article'],
    \MaikSchneider\CampT3api\Domain\Model\Author::class => ['tableName' => 'tx_campdemo_author'],
    \MaikSchneider\CampT3api\Domain\Model\Tag::class => ['tableName' => 'tx_campdemo_tag'],
    \MaikSchneider\CampT3api\Domain\Model\Category::class => ['tableName' => 'sys_category'],
];
