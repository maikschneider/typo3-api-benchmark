<?php

declare(strict_types=1);

namespace MaikSchneider\CampExtbase\Domain\Repository;

use MaikSchneider\CampExtbase\Domain\Model\Article;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Article>
 */
class ArticleRepository extends Repository
{
    protected $defaultOrderings = ['uid' => QueryInterface::ORDER_ASCENDING];
}
