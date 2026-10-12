<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Bench\Database;

/**
 * Process-wide counter, static because DBAL instantiates driver middlewares outside the DI container.
 */
final class QueryCounter
{
    private static int $count = 0;

    /** @var list<string> */
    private static array $log = [];

    public static function increment(string $sql): void
    {
        self::$count++;
        self::$log[] = $sql;
    }

    public static function reset(): void
    {
        self::$count = 0;
        self::$log = [];
    }

    public static function get(): int
    {
        return self::$count;
    }

    /**
     * @return list<string>
     */
    public static function log(): array
    {
        return self::$log;
    }
}
