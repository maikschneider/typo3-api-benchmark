<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Domain\Repository;

use Typo3ApiBenchmark\Nnrestapi\Domain\Model\ArticleDepth;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<ArticleDepth>
 */
class ArticleDepthRepository extends Repository
{
    protected $defaultOrderings = ['uid' => QueryInterface::ORDER_ASCENDING];
}
