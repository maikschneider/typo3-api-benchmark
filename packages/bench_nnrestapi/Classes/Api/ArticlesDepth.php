<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Api;

use Nng\Nnrestapi\Annotations as Api;
use Typo3ApiBenchmark\Nnrestapi\Domain\Repository\ArticleDepthRepository;

/**
 * Articles with the relation chain author → organization → country resolved to the given depth.
 *
 * @Api\Endpoint()
 */
class ArticlesDepth extends AbstractCachedApi
{
    private ArticleDepthRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = \nn\t3::injectClass(ArticleDepthRepository::class);
        \nn\t3::Db()->ignoreEnableFields($this->articleRepository);
    }

    /**
     * @Api\Route("GET /articles-depth/{depth}")
     * @Api\Access("public")
     */
    public function getCollection(int $depth): array
    {
        $arguments = $this->request->getArguments();
        $depth = min(3, max(0, $depth));
        $page = max(1, (int)($arguments['page'] ?? 1));
        $size = min(1000, max(1, (int)($arguments['itemsPerPage'] ?? 20)));

        // The response wraps the articles in two array levels.
        return $this->cached([__METHOD__ => [$depth, $page, $size]], function () use ($page, $size): array {
            $query = $this->articleRepository->createQuery();
            return [
                'totalItems' => $query->count(),
                'member' => $query->setLimit($size)->setOffset(($page - 1) * $size)->execute(),
            ];
        }, $depth + 2);
    }
}
