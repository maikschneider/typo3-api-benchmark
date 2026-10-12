<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\T3api\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Author extends AbstractEntity
{
    /**
     * @T3api\Serializer\Groups({"bench_article"})
     */
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }
}
