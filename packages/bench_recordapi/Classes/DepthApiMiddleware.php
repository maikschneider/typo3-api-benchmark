<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Recordapi;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final readonly class DepthApiMiddleware implements MiddlewareInterface
{
    private const CACHE_TAGS = ['tx_bench_article', 'tx_bench_author', 'tx_bench_organization', 'tx_bench_country'];

    public function __construct(
        private DepthSerializer $serializer,
        private FrontendInterface $cache,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!preg_match('#^/_recordapi/articles-depth-([0-3])$#', rtrim($request->getUri()->getPath(), '/'), $matches)) {
            return $handler->handle($request);
        }

        $params = $request->getQueryParams();
        $depth = (int)$matches[1];
        $limit = max(1, min(1000, (int)($params['itemsPerPage'] ?? 20)));
        $page = max(1, (int)($params['page'] ?? 1));

        $cacheIdentifier = sha1('depth-' . $depth . '-' . $page . '-' . $limit);
        $data = $this->cache->get($cacheIdentifier);
        $cacheStatus = 'HIT';
        if ($data === false) {
            $cacheStatus = 'MISS';
            $data = $this->serializer->collection($depth, $limit, ($page - 1) * $limit);
            $this->cache->set($cacheIdentifier, $data, self::CACHE_TAGS);
        }

        return (new JsonResponse($data))->withHeader('X-Cache', $cacheStatus);
    }
}
