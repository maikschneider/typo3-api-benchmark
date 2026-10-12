<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Extbase\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Author extends AbstractEntity
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }
}
