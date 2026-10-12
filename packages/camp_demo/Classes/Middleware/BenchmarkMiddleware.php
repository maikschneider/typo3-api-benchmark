<?php

declare(strict_types=1);

namespace MaikSchneider\CampDemo\Middleware;

use MaikSchneider\CampDemo\Benchmark\ExtbaseArticleSerializer;
use MaikSchneider\CampDemo\Benchmark\RecordApiArticleSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * The same article collection as /_api/articles with embedded author, tags and
 * categories, built the way a hand-written endpoint would build it.
 */
final readonly class BenchmarkMiddleware implements MiddlewareInterface
{
    private const PREFIX = '/_bench/';

    public function __construct(
        private RecordApiArticleSerializer $recordApi,
        private ExtbaseArticleSerializer $extbase,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (!str_starts_with($path, self::PREFIX)) {
            return $handler->handle($request);
        }

        $params = $request->getQueryParams();
        $limit = max(1, min(500, (int)($params['itemsPerPage'] ?? 20)));
        $page = max(1, (int)($params['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $result = match (trim(substr($path, strlen(self::PREFIX)), '/')) {
            'record-api/articles' => $this->recordApi->serialize($limit, $offset),
            'extbase/articles' => $this->extbase->serialize($limit, $offset),
            default => null,
        };

        if ($result === null) {
            return new JsonResponse(['error' => 'Unknown benchmark, use record-api/articles or extbase/articles'], 404);
        }

        return new JsonResponse($result);
    }
}
