<?php

declare(strict_types=1);

namespace MaikSchneider\CampDemo\Database;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

final class QueryCountingDriverMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new class ($driver) extends AbstractDriverMiddleware {
            public function connect(array $params): Connection
            {
                return new class (parent::connect($params)) extends AbstractConnectionMiddleware {
                    public function prepare(string $sql): Statement
                    {
                        return new class (parent::prepare($sql), $sql) extends AbstractStatementMiddleware {
                            public function __construct(Statement $wrapped, private readonly string $sql)
                            {
                                parent::__construct($wrapped);
                            }

                            public function execute(): Result
                            {
                                QueryCounter::increment($this->sql);
                                return parent::execute();
                            }
                        };
                    }

                    public function query(string $sql): Result
                    {
                        QueryCounter::increment($sql);
                        return parent::query($sql);
                    }

                    public function exec(string $sql): int|string
                    {
                        QueryCounter::increment($sql);
                        return parent::exec($sql);
                    }
                };
            }
        };
    }
}
