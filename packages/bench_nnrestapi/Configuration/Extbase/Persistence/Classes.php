<?php

declare(strict_types=1);

return [
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\Article::class => [
        'tableName' => 'tx_bench_article',
        'properties' => ['reading_time' => ['fieldName' => 'reading_time']],
    ],
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\Author::class => ['tableName' => 'tx_bench_author'],
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\Tag::class => ['tableName' => 'tx_bench_tag'],
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\Category::class => ['tableName' => 'sys_category'],
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\ArticleDepth::class => [
        'tableName' => 'tx_bench_article',
        'properties' => ['reading_time' => ['fieldName' => 'reading_time']],
    ],
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\AuthorDepth::class => ['tableName' => 'tx_bench_author'],
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\OrganizationDepth::class => ['tableName' => 'tx_bench_organization'],
    \Typo3ApiBenchmark\Nnrestapi\Domain\Model\CountryDepth::class => ['tableName' => 'tx_bench_country'],
];
