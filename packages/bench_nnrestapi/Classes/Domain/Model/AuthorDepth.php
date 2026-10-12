<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class AuthorDepth extends AbstractEntity
{
    protected string $name = '';

    #[Lazy]
    protected OrganizationDepth|LazyLoadingProxy|null $organization = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrganization(): ?OrganizationDepth
    {
        if ($this->organization instanceof LazyLoadingProxy) {
            $this->organization = $this->organization->_loadRealInstance();
        }
        return $this->organization;
    }
}
