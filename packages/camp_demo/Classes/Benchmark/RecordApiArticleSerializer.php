<?php

declare(strict_types=1);

namespace MaikSchneider\CampDemo\Benchmark;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Domain\Persistence\RecordIdentityMap;
use TYPO3\CMS\Core\Domain\RecordInterface;

/**
 * Core Record API: typed, lazily resolved records as recommended for Fluid since v13.
 */
final readonly class RecordApiArticleSerializer
{
    private const TABLE = 'tx_campdemo_article';

    public function __construct(
        private ConnectionPool $connectionPool,
        private RecordFactory $recordFactory,
        private Context $context,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function serialize(int $limit, int $offset): array
    {
        $total = (int)$this->connectionPool->getQueryBuilderForTable(self::TABLE)
            ->count('uid')
            ->from(self::TABLE)
            ->executeQuery()
            ->fetchOne();

        $rows = $this->connectionPool->getQueryBuilderForTable(self::TABLE)
            ->select('*')
            ->from(self::TABLE)
            ->orderBy('uid')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->executeQuery()
            ->fetchAllAssociative();

        $identityMap = new RecordIdentityMap();
        $members = [];
        foreach ($rows as $row) {
            $article = $this->recordFactory->createResolvedRecordFromDatabaseRow(self::TABLE, $row, $this->context, $identityMap);
            $members[] = [
                'uid' => $article->getUid(),
                'title' => $article->get('title'),
                'teaser' => $article->get('teaser'),
                'author' => $this->mapRecords($article->get('author'), 'name')[0] ?? null,
                'tags' => $this->mapRecords($article->get('tags'), 'title'),
                'categories' => $this->mapRecords($article->get('categories'), 'title'),
            ];
        }

        return ['totalItems' => $total, 'member' => $members];
    }

    /**
     * @return list<array{uid: int, label: mixed}>
     */
    private function mapRecords(mixed $value, string $labelField): array
    {
        $records = match (true) {
            $value instanceof RecordInterface => [$value],
            is_iterable($value) => $value,
            default => [],
        };

        $result = [];
        foreach ($records as $record) {
            $result[] = ['uid' => $record->getUid(), $labelField => $record->get($labelField)];
        }
        return $result;
    }
}
