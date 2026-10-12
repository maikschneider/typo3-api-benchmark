<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Bench\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

#[AsCommand(name: 'bench:seed', description: 'Replace all demo records with a deterministic data set')]
final class SeedCommand extends Command
{
    private const AUTHORS = [
        'Ada Lovelace', 'Grace Hopper', 'Linus Torvalds', 'Margaret Hamilton', 'Rasmus Lerdorf',
        'Kasper Skårhøj', 'Barbara Liskov', 'Ken Thompson', 'Radia Perlman', 'Tim Berners-Lee',
    ];

    private const TAGS = [
        'API', 'Performance', 'Caching', 'Headless', 'Fluid', 'TCA', 'Extbase', 'DataHandler',
        'Security', 'Testing', 'DDEV', 'Composer', 'PHPStan', 'Site Sets', 'OpenAPI',
    ];

    private const CATEGORIES = ['News', 'Tutorial', 'Release', 'Community', 'Opinion', 'Case Study', 'Event', 'Deep Dive'];

    private const TOPICS = [
        'Content Blocks', 'the Record API', 'Site Sets', 'Fluid Components', 'the Schema API',
        'Workspaces', 'the DataHandler', 'Event Listeners', 'Middlewares', 'Scheduler Tasks',
    ];

    private const VERBS = ['Understanding', 'Mastering', 'Debugging', 'Upgrading', 'Testing', 'Scaling', 'Rethinking'];

    private const TABLES = [
        'tx_bench_article', 'tx_bench_author', 'tx_bench_tag', 'tx_bench_article_tag_mm',
    ];

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('articles', 'a', InputOption::VALUE_REQUIRED, 'Number of articles', '10000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $articleCount = max(1, (int)$input->getOption('articles'));
        mt_srand(26);

        $storagePid = $this->ensureStorageFolder();
        $this->truncate();
        $now = time();
        $base = ['pid' => $storagePid, 'tstamp' => $now, 'crdate' => $now];

        $authorUids = [];
        foreach (self::AUTHORS as $name) {
            $authorUids[] = $this->insert('tx_bench_author', $base + ['name' => $name, 'bio' => $name . ' writes about TYPO3.']);
        }
        $tagUids = [];
        foreach (self::TAGS as $title) {
            $tagUids[] = $this->insert('tx_bench_tag', $base + ['title' => $title]);
        }
        $categoryUids = [];
        foreach (self::CATEGORIES as $title) {
            $categoryUids[] = $this->insert('sys_category', $base + ['title' => $title]);
        }

        $tagMm = [];
        $categoryMm = [];
        for ($i = 1; $i <= $articleCount; $i++) {
            $articleTags = $this->pick($tagUids, mt_rand(2, 4));
            $articleCategories = $this->pick($categoryUids, mt_rand(1, 3));
            $topic = self::TOPICS[mt_rand(0, count(self::TOPICS) - 1)];
            $articleUid = $this->insert('tx_bench_article', $base + [
                'title' => sprintf('%s %s, part %d', self::VERBS[mt_rand(0, count(self::VERBS) - 1)], $topic, $i),
                'teaser' => sprintf('A practical look at %s in TYPO3 v14.', $topic),
                'published' => $now - $i * 86400,
                'reading_time' => mt_rand(3, 25),
                'author' => $authorUids[mt_rand(0, count($authorUids) - 1)],
                'tags' => count($articleTags),
                'categories' => count($articleCategories),
            ]);
            foreach (array_values($articleTags) as $sorting => $tagUid) {
                $tagMm[] = [$articleUid, $tagUid, $sorting + 1];
            }
            foreach (array_values($articleCategories) as $sorting => $categoryUid) {
                $categoryMm[] = [$categoryUid, $articleUid, 'tx_bench_article', 'categories', $sorting + 1];
            }
        }

        $this->connectionPool->getConnectionForTable('tx_bench_article_tag_mm')
            ->bulkInsert('tx_bench_article_tag_mm', $tagMm, ['uid_local', 'uid_foreign', 'sorting']);
        $this->connectionPool->getConnectionForTable('sys_category_record_mm')
            ->bulkInsert('sys_category_record_mm', $categoryMm, ['uid_local', 'uid_foreign', 'tablenames', 'fieldname', 'sorting_foreign']);

        $output->writeln(sprintf(
            'Seeded %d articles, %d authors, %d tags, %d categories on page %d.',
            $articleCount,
            count($authorUids),
            count($tagUids),
            count($categoryUids),
            $storagePid,
        ));

        return Command::SUCCESS;
    }

    private function ensureStorageFolder(): int
    {
        $connection = $this->connectionPool->getConnectionForTable('pages');
        $uid = $connection->select(['uid'], 'pages', ['title' => 'Articles', 'doktype' => 254, 'deleted' => 0])->fetchOne();
        if ($uid !== false) {
            return (int)$uid;
        }
        return $this->insert('pages', ['pid' => 1, 'title' => 'Articles', 'doktype' => 254, 'tstamp' => time(), 'crdate' => time()]);
    }

    private function truncate(): void
    {
        foreach (self::TABLES as $table) {
            $this->connectionPool->getConnectionForTable($table)->truncate($table);
        }
        $this->connectionPool->getConnectionForTable('sys_category_record_mm')
            ->delete('sys_category_record_mm', ['tablenames' => 'tx_bench_article']);
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->delete('sys_category')
            ->where($queryBuilder->expr()->in('title', $queryBuilder->createNamedParameter(self::CATEGORIES, Connection::PARAM_STR_ARRAY)))
            ->executeStatement();
    }

    /**
     * @param array<string, int|string> $row
     */
    private function insert(string $table, array $row): int
    {
        $connection = $this->connectionPool->getConnectionForTable($table);
        $connection->insert($table, $row);
        return (int)$connection->lastInsertId();
    }

    /**
     * @param list<int> $uids
     * @return array<int, int>
     */
    private function pick(array $uids, int $count): array
    {
        $keys = (array)array_rand($uids, $count);
        return array_intersect_key($uids, array_flip($keys));
    }
}
