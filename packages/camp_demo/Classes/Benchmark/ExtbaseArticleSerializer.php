<?php

declare(strict_types=1);

namespace MaikSchneider\CampDemo\Benchmark;

use MaikSchneider\CampDemo\Domain\Model\Article;
use MaikSchneider\CampDemo\Domain\Repository\ArticleRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Extbase repository and domain models, the persistence layer under EXT:t3api and most nnrestapi endpoints.
 */
final readonly class ExtbaseArticleSerializer
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private Context $context,
        private ConfigurationManagerInterface $configurationManager,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function serialize(int $limit, int $offset): array
    {
        $querySettings = new Typo3QuerySettings($this->context, $this->configurationManager);
        $querySettings->setRespectStoragePage(false);
        $this->articleRepository->setDefaultQuerySettings($querySettings);

        $query = $this->articleRepository->createQuery();
        $total = $query->count();

        $query = $this->articleRepository->createQuery();
        $query->setOrderings(['uid' => 'ASC'])->setLimit($limit)->setOffset($offset);

        $members = [];
        /** @var Article $article */
        foreach ($query->execute() as $article) {
            $author = $article->getAuthor();
            $members[] = [
                'uid' => $article->getUid(),
                'title' => $article->getTitle(),
                'teaser' => $article->getTeaser(),
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

        return ['totalItems' => $total, 'member' => $members];
    }
}
