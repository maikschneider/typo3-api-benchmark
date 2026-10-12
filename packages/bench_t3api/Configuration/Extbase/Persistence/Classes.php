<?php

declare(strict_types=1);

return [
    \Typo3ApiBenchmark\T3api\Domain\Model\Article::class => ['tableName' => 'tx_bench_article'],
    \Typo3ApiBenchmark\T3api\Domain\Model\Author::class => ['tableName' => 'tx_bench_author'],
    \Typo3ApiBenchmark\T3api\Domain\Model\Tag::class => ['tableName' => 'tx_bench_tag'],
    \Typo3ApiBenchmark\T3api\Domain\Model\Category::class => ['tableName' => 'sys_category'],
];
