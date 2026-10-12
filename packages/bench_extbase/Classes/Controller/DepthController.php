<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Extbase\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Typo3ApiBenchmark\Extbase\Domain\Model\Article;
use Typo3ApiBenchmark\Extbase\Domain\Repository\ArticleRepository;

/**
 * Articles with the relation chain author → organization → country resolved to the given depth.
 */
final class DepthController extends ActionController
{
    private const CACHE_TAGS = ['tx_bench_article', 'tx_bench_author', 'tx_bench_organization', 'tx_bench_country'];

    public function __construct(private readonly ArticleRepository $articleRepository) {}

    protected function initializeAction(): void
    {
        $this->request->getAttribute('frontend.cache.collector')->addCacheTags(
            ...array_map(static fn(string $tag): CacheTag => new CacheTag($tag), self::CACHE_TAGS),
        );
    }

    public function listAction(int $depth = 1, int $page = 1, int $size = 20): ResponseInterface
    {
        $query = $this->articleRepository->createQuery();
        $total = $query->count();
        $articles = $query->setLimit($size)->setOffset(($page - 1) * $size)->execute();

        $members = [];
        /** @var Article $article */
        foreach ($articles as $article) {
            $member = [
                'uid' => $article->getUid(),
                'title' => $article->getTitle(),
                'teaser' => $article->getTeaser(),
                'published' => $article->getPublished()?->format(\DateTimeInterface::ATOM),
                'reading_time' => $article->getReadingTime(),
            ];
            $author = $article->getAuthor();
            if ($depth >= 1 && $author !== null) {
                $member['author'] = ['uid' => $author->getUid(), 'name' => $author->getName()];
                $organization = $author->getOrganization();
                if ($depth >= 2 && $organization !== null) {
                    $member['author']['organization'] = ['uid' => $organization->getUid(), 'name' => $organization->getName()];
                    $country = $organization->getCountry();
                    if ($depth >= 3 && $country !== null) {
                        $member['author']['organization']['country'] = ['uid' => $country->getUid(), 'title' => $country->getTitle()];
                    }
                }
            }
            $members[] = $member;
        }

        return $this->jsonResponse((string)json_encode(['totalItems' => $total, 'member' => $members]));
    }
}
