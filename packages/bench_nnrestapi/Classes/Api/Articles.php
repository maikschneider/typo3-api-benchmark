<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Api;

use Nng\Nnrestapi\Annotations as Api;
use Typo3ApiBenchmark\Nnrestapi\Domain\Repository\ArticleRepository;

/**
 * @Api\Endpoint()
 */
class Articles extends AbstractCachedApi
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = \nn\t3::injectClass(ArticleRepository::class);
        \nn\t3::Db()->ignoreEnableFields($this->articleRepository);
    }

    /**
     * @Api\Route("GET /articles")
     * @Api\Access("public")
     */
    public function getCollection(): array
    {
        $arguments = $this->request->getArguments();
        $page = max(1, (int)($arguments['page'] ?? 1));
        $size = min(100, max(1, (int)($arguments['itemsPerPage'] ?? 20)));

        return $this->cached([__METHOD__ => [$page, $size]], function () use ($page, $size): array {
            $query = $this->articleRepository->createQuery();
            return [
                'totalItems' => $query->count(),
                'member' => $query->setLimit($size)->setOffset(($page - 1) * $size)->execute(),
            ];
        });
    }

    /**
     * @Api\Route("GET /articles/{uid}")
     * @Api\Access("public")
     */
    public function getItem(int $uid)
    {
        return $this->cached([__METHOD__ => $uid], fn() => $this->articleRepository->findByUid($uid))
            ?? $this->response->notFound("Article {$uid} not found.");
    }
}
