<?php

declare(strict_types=1);

namespace MaikSchneider\CampDemo\Middleware;

use MaikSchneider\CampDemo\Database\QueryCounter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Core\Environment;

/**
 * Runs first in the frontend stack, so every API is measured from the same point:
 * executed queries, wall time and peak memory of the request. Send
 * "X-Debug-Queries: 1" to write the executed SQL to var/log/queries.log.
 */
final class QueryCountMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        QueryCounter::reset();
        $start = hrtime(true);

        $response = $handler->handle($request);

        $duration = (hrtime(true) - $start) / 1e6;

        if ($request->getHeaderLine('X-Debug-Queries') === '1') {
            file_put_contents(Environment::getVarPath() . '/log/queries.log', implode("\n", QueryCounter::log()) . "\n");
        }

        return $response
            ->withHeader('X-Query-Count', (string)QueryCounter::get())
            ->withHeader('Server-Timing', sprintf('app;dur=%.1f', $duration))
            ->withHeader('X-Memory-Peak', (string)memory_get_peak_usage());
    }
}
