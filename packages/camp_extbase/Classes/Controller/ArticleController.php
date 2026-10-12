<?php

declare(strict_types=1);

namespace MaikSchneider\CampExtbase\Controller;

use MaikSchneider\CampExtbase\Domain\Model\Article;
use MaikSchneider\CampExtbase\Domain\Repository\ArticleRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class ArticleController extends ActionController
{
    /**
     * DataHandler flushes these table tags from the page cache whenever a record of the table changes.
     */
    private const CACHE_TAGS = ['tx_campdemo_article', 'tx_campdemo_author', 'tx_campdemo_tag', 'sys_category'];

    public function __construct(private readonly ArticleRepository $articleRepository) {}

    protected function initializeAction(): void
    {
        $this->request->getAttribute('frontend.cache.collector')->addCacheTags(
            ...array_map(static fn(string $tag): CacheTag => new CacheTag($tag), self::CACHE_TAGS),
        );
    }

    public function listAction(int $page = 1, int $size = 20): ResponseInterface
    {
        $query = $this->articleRepository->createQuery();
        $total = $query->count();
        $articles = $query->setLimit($size)->setOffset(($page - 1) * $size)->execute();

        $members = [];
        foreach ($articles as $article) {
            $members[] = $this->toArray($article);
        }

        return $this->jsonResponse((string)json_encode(['totalItems' => $total, 'member' => $members]));
    }

    public function showAction(Article $article): ResponseInterface
    {
        return $this->jsonResponse((string)json_encode($this->toArray($article)));
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(Article $article): array
    {
        $author = $article->getAuthor();

        return [
            'uid' => $article->getUid(),
            'title' => $article->getTitle(),
            'teaser' => $article->getTeaser(),
            'published' => $article->getPublished()?->format(\DateTimeInterface::ATOM),
            'reading_time' => $article->getReadingTime(),
            'author' => $author ? ['uid' => $author->getUid(), 'name' => $author->getName()] : null,
            'tags' => array_map(
                static fn($tag): array => ['uid' => $tag->getUid(), 'title' => $tag->getTitle()],
                $article->getTags()->toArray(),
            ),
            'categories' => array_map(
                static fn($category): array => ['uid' => $category->getUid(), 'title' => $category->getTitle()],
                $article->getCategories()->toArray(),
            ),
        ];
    }
}
