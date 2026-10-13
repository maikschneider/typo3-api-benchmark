<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\T3api\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use SourceBroker\T3api\Filter\OrderFilter;
use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * Articles with the relation chain author → organization → country, one operation per depth.
 *
 * @T3api\ApiResource(
 *     collectionOperations={
 *          "get_depth0"={"path"="/articles-depth-0", "normalizationContext"={"groups"={"depth0"}}},
 *          "get_depth1"={"path"="/articles-depth-1", "normalizationContext"={"groups"={"depth1"}}},
 *          "get_depth2"={"path"="/articles-depth-2", "normalizationContext"={"groups"={"depth2"}}},
 *          "get_depth3"={"path"="/articles-depth-3", "normalizationContext"={"groups"={"depth3"}}},
 *     },
 *     itemOperations={
 *          "get"={"path"="/articles-depth/{id}", "normalizationContext"={"groups"={"depth3"}}},
 *     },
 *     attributes={
 *          "pagination_items_per_page"=20,
 *          "pagination_client_items_per_page"=true,
 *          "maximum_items_per_page"=1000,
 *          "persistence"={"storagePid"="2"},
 *          "cache"={"enabled"=true}
 *     }
 * )
 *
 * @T3api\ApiFilter(OrderFilter::class, properties={"uid": "asc"})
 */
class ArticleDepth extends AbstractEntity
{
    /**
     * @T3api\Serializer\Groups({"depth0", "depth1", "depth2", "depth3"})
     */
    protected string $title = '';

    /**
     * @T3api\Serializer\Groups({"depth0", "depth1", "depth2", "depth3"})
     */
    protected string $teaser = '';

    /**
     * @T3api\Serializer\Groups({"depth0", "depth1", "depth2", "depth3"})
     */
    protected ?\DateTime $published = null;

    /**
     * @T3api\Serializer\Groups({"depth0", "depth1", "depth2", "depth3"})
     * @T3api\Serializer\SerializedName("reading_time")
     */
    protected int $readingTime = 0;

    /**
     * @T3api\Serializer\Groups({"depth1", "depth2", "depth3"})
     */
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

    public function getPublished(): ?\DateTime
    {
        return $this->published;
    }

    public function getReadingTime(): int
    {
        return $this->readingTime;
    }

    public function getAuthor(): ?AuthorDepth
    {
        if ($this->author instanceof LazyLoadingProxy) {
            $this->author = $this->author->_loadRealInstance();
        }
        return $this->author;
    }
}
