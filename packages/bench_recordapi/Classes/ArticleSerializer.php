<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Recordapi;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Persistence\RecordIdentityMap;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Domain\RecordInterface;

final readonly class ArticleSerializer
{
    public const TABLE = 'tx_bench_article';

    public function __construct(
        private ConnectionPool $connectionPool,
        private RecordFactory $recordFactory,
        private Context $context,
    ) {}

    /**
     * @return array{totalItems: int, member: list<array<string, mixed>>}
     */
    public function collection(int $limit, int $offset): array
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
            $members[] = $this->toArray($this->recordFactory->createResolvedRecordFromDatabaseRow(self::TABLE, $row, $this->context, $identityMap));
        }

        return ['totalItems' => $total, 'member' => $members];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function item(int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $row = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : $this->toArray($this->recordFactory->createResolvedRecordFromDatabaseRow(self::TABLE, $row, $this->context));
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(RecordInterface $article): array
    {
        $published = $article->get('published');

        return [
            'uid' => $article->getUid(),
            'title' => $article->get('title'),
            'teaser' => $article->get('teaser'),
            'published' => $published instanceof \DateTimeInterface ? $published->format(\DateTimeInterface::ATOM) : null,
            'reading_time' => $article->get('reading_time'),
            'author' => $this->mapRecords($article->get('author'), 'name')[0] ?? null,
            'tags' => $this->mapRecords($article->get('tags'), 'title'),
            'categories' => $this->mapRecords($article->get('categories'), 'title'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
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
