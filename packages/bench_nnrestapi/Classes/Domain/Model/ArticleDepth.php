<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class ArticleDepth extends AbstractEntity
{
    protected string $title = '';

    protected string $teaser = '';

    protected int $published = 0;

    /**
     * Public under the column name, because nnrestapi uses gettable property names as JSON keys.
     */
    public int $reading_time = 0;

    #[Lazy]
    protected AuthorDepth|LazyLoadingProxy|null $author = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTeaser(): string
    {
        return $this->teaser;
    }

    public function getPublished(): int
    {
        return $this->published;
    }

    public function getAuthor(): ?AuthorDepth
    {
        if ($this->author instanceof LazyLoadingProxy) {
            $this->author = $this->author->_loadRealInstance();
        }
        return $this->author;
    }
}
