<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Extbase\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Article extends AbstractEntity
{
    protected string $title = '';

    protected string $teaser = '';

    protected ?\DateTime $published = null;

    protected int $readingTime = 0;

    #[Lazy]
    protected Author|LazyLoadingProxy|null $author = null;

    /**
     * @var ObjectStorage<Tag>
     */
    #[Lazy]
    protected ObjectStorage $tags;

    /**
     * @var ObjectStorage<Category>
     */
    #[Lazy]
    protected ObjectStorage $categories;

    public function __construct()
    {
        $this->tags = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->tags ??= new ObjectStorage();
        $this->categories ??= new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTeaser(): string
    {
        return $this->teaser;
    }

    public function getPublished(): ?\DateTime
    {
        return $this->published;
    }

    public function getReadingTime(): int
    {
        return $this->readingTime;
    }

    public function getAuthor(): ?Author
    {
        if ($this->author instanceof LazyLoadingProxy) {
            $this->author = $this->author->_loadRealInstance();
        }
        return $this->author;
    }

    /**
     * @return ObjectStorage<Tag>
     */
    public function getTags(): ObjectStorage
    {
        return $this->tags;
    }

    /**
     * @return ObjectStorage<Category>
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }
}
