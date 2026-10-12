<?php

declare(strict_types=1);

return [
    \Typo3ApiBenchmark\Extbase\Domain\Model\Article::class => ['tableName' => 'tx_bench_article'],
    \Typo3ApiBenchmark\Extbase\Domain\Model\Author::class => ['tableName' => 'tx_bench_author'],
    \Typo3ApiBenchmark\Extbase\Domain\Model\Tag::class => ['tableName' => 'tx_bench_tag'],
    \Typo3ApiBenchmark\Extbase\Domain\Model\Category::class => ['tableName' => 'sys_category'],
    \Typo3ApiBenchmark\Extbase\Domain\Model\Organization::class => ['tableName' => 'tx_bench_organization'],
    \Typo3ApiBenchmark\Extbase\Domain\Model\Country::class => ['tableName' => 'tx_bench_country'],
];
