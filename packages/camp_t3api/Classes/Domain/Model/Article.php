<?php

declare(strict_types=1);

namespace MaikSchneider\CampT3api\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use SourceBroker\T3api\Filter\OrderFilter;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @T3api\ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "path"="/articles",
 *              "normalizationContext"={
 *                  "groups"={"camp_article"}
 *              },
 *          },
 *     },
 *     itemOperations={
 *          "get"={
 *              "path"="/articles/{id}",
 *              "normalizationContext"={
 *                  "groups"={"camp_article"}
 *              },
 *          },
 *     },
 *     attributes={
 *          "pagination_items_per_page"=20,
 *          "pagination_client_items_per_page"=true,
 *          "maximum_items_per_page"=100,
 *          "persistence"={
 *              "storagePid"="2"
 *          },
 *          "cache"={
 *              "enabled"=true
 *          }
 *     }
 * )
 *
 * @T3api\ApiFilter(
 *     OrderFilter::class,
 *     properties={"uid": "asc"}
 * )
 */
class Article extends AbstractEntity
{
    /**
     * @T3api\Serializer\Groups({"camp_article"})
     */
    protected string $title = '';

    /**
     * @T3api\Serializer\Groups({"camp_article"})
     */
    protected string $teaser = '';

    /**
     * @T3api\Serializer\Groups({"camp_article"})
     */
    protected ?\DateTime $published = null;

    /**
     * @T3api\Serializer\Groups({"camp_article"})
     * @T3api\Serializer\SerializedName("reading_time")
     */
    protected int $readingTime = 0;

    /**
     * @T3api\Serializer\Groups({"camp_article"})
     */
    protected ?Author $author = null;

    /**
     * @var ObjectStorage<Tag>
     * @T3api\Serializer\Groups({"camp_article"})
     */
    protected ObjectStorage $tags;

    /**
     * @var ObjectStorage<Category>
     * @T3api\Serializer\Groups({"camp_article"})
     */
    protected ObjectStorage $categories;

    public function __construct()
    {
        $this->initializeObject();
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
