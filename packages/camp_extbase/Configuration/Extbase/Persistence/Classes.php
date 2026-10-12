<?php

declare(strict_types=1);

return [
    \MaikSchneider\CampExtbase\Domain\Model\Article::class => ['tableName' => 'tx_campdemo_article'],
    \MaikSchneider\CampExtbase\Domain\Model\Author::class => ['tableName' => 'tx_campdemo_author'],
    \MaikSchneider\CampExtbase\Domain\Model\Tag::class => ['tableName' => 'tx_campdemo_tag'],
    \MaikSchneider\CampExtbase\Domain\Model\Category::class => ['tableName' => 'sys_category'],
];
