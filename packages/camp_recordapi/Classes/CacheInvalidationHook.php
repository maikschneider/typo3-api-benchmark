<?php

declare(strict_types=1);

namespace MaikSchneider\CampRecordapi;

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

final readonly class CacheInvalidationHook
{
    public function __construct(private FrontendInterface $cache) {}

    /**
     * @param array{table?: string, tags?: array<string, mixed>} $params
     */
    public function clearCachePostProc(array $params): void
    {
        $tables = array_keys($params['tags'] ?? []);
        if (isset($params['table'])) {
            $tables[] = $params['table'];
        }
        $this->cache->flushByTags(array_values(array_unique(array_filter($tables, is_string(...)))));
    }
}
