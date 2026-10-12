<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Api;

use Nng\Nnrestapi\Api\AbstractApi;
use Nng\Nnrestapi\Distiller\ModelDistiller;

/**
 * `@Api\Cache` keys its entries by class and method only, so endpoints with arguments use the
 * nnhelpers cache directly, as the nnrestapi documentation describes for that case. The cache stores
 * JSON, so the models go through the same conversion and global distillers as an uncached response.
 */
abstract class AbstractCachedApi extends AbstractApi
{
    protected function cached(array $identifier, callable $load, int $depth = 10): ?array
    {
        if ($data = \nn\t3::Cache()->get($identifier)) {
            return $data;
        }
        $result = $load();
        if ($result === null) {
            return null;
        }
        $data = \nn\t3::Convert($result)->toArray($depth);
        ModelDistiller::process($result, $data);
        return \nn\t3::Cache()->set($identifier, $data);
    }
}
