<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class CountryDepth extends AbstractEntity
{
    protected string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }
}
