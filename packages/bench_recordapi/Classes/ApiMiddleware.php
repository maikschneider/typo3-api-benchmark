<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Recordapi;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final readonly class ApiMiddleware implements MiddlewareInterface
{
    private const PATH = '/_recordapi/articles';

    /**
     * Every table the response is built from, flushed by CacheInvalidationHook.
     */
    private const CACHE_TAGS = ['tx_bench_article', 'tx_bench_author', 'tx_bench_tag', 'sys_category'];

    public function __construct(
        private ArticleSerializer $serializer,
        private FrontendInterface $cache,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = rtrim($request->getUri()->getPath(), '/');
        if ($path !== self::PATH && !preg_match('#^' . self::PATH . '/(\d+)$#', $path, $matches)) {
            return $handler->handle($request);
        }

        $params = $request->getQueryParams();
        $limit = max(1, min(100, (int)($params['itemsPerPage'] ?? 20)));
        $page = max(1, (int)($params['page'] ?? 1));
        $uid = (int)($matches[1] ?? 0);

        $cacheIdentifier = sha1($uid > 0 ? 'item-' . $uid : 'list-' . $page . '-' . $limit);
        $data = $this->cache->get($cacheIdentifier);
        $cacheStatus = 'HIT';

        if ($data === false) {
            $cacheStatus = 'MISS';
            $data = $uid > 0 ? $this->serializer->item($uid) : $this->serializer->collection($limit, ($page - 1) * $limit);
            if ($data === null) {
                return new JsonResponse(['error' => 'Not found'], 404);
            }
            $this->cache->set($cacheIdentifier, $data, self::CACHE_TAGS);
        }

        return (new JsonResponse($data))->withHeader('X-Cache', $cacheStatus);
    }
}
