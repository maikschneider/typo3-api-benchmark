<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Extbase\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class Author extends AbstractEntity
{
    protected string $name = '';

    #[Lazy]
    protected Organization|LazyLoadingProxy|null $organization = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrganization(): ?Organization
    {
        if ($this->organization instanceof LazyLoadingProxy) {
            $this->organization = $this->organization->_loadRealInstance();
        }
        return $this->organization;
    }
}
