<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Recordapi;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Persistence\RecordIdentityMap;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Domain\RecordInterface;

/**
 * Articles with the relation chain author → organization → country resolved to the given depth.
 */
final readonly class DepthSerializer
{
    public function __construct(
        private ConnectionPool $connectionPool,
        private RecordFactory $recordFactory,
        private Context $context,
    ) {}

    /**
     * @return array{totalItems: int, member: list<array<string, mixed>>}
     */
    public function collection(int $depth, int $limit, int $offset): array
    {
        $table = ArticleSerializer::TABLE;
        $total = (int)$this->connectionPool->getQueryBuilderForTable($table)->count('uid')->from($table)->executeQuery()->fetchOne();
        $rows = $this->connectionPool->getQueryBuilderForTable($table)
            ->select('*')
            ->from($table)
            ->orderBy('uid')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->executeQuery()
            ->fetchAllAssociative();

        $identityMap = new RecordIdentityMap();
        $members = [];
        foreach ($rows as $row) {
            $article = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $row, $this->context, $identityMap);
            $member = [
                'uid' => $article->getUid(),
                'title' => $article->get('title'),
                'teaser' => $article->get('teaser'),
                'published' => $article->get('published')?->format(\DateTimeInterface::ATOM),
                'reading_time' => $article->get('reading_time'),
            ];
            if ($depth >= 1) {
                $member['author'] = $this->author($article->get('author'), $depth);
            }
            $members[] = $member;
        }

        return ['totalItems' => $total, 'member' => $members];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function author(mixed $author, int $depth): ?array
    {
        if (!$author instanceof RecordInterface) {
            return null;
        }
        $result = ['uid' => $author->getUid(), 'name' => $author->get('name')];
        $organization = $author->get('organization');
        if ($depth >= 2 && $organization instanceof RecordInterface) {
            $result['organization'] = ['uid' => $organization->getUid(), 'name' => $organization->get('name')];
            $country = $organization->get('country');
            if ($depth >= 3 && $country instanceof RecordInterface) {
                $result['organization']['country'] = ['uid' => $country->getUid(), 'title' => $country->get('title')];
            }
        }
        return $result;
    }
}
