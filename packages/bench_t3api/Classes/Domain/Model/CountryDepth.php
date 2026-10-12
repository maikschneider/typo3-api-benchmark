<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\T3api\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class CountryDepth extends AbstractEntity
{
    /**
     * @T3api\Serializer\Groups({"depth3"})
     */
    protected string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }
}
