<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\Nnrestapi\Domain\Model;

use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class OrganizationDepth extends AbstractEntity
{
    protected string $name = '';

    #[Lazy]
    protected CountryDepth|LazyLoadingProxy|null $country = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function getCountry(): ?CountryDepth
    {
        if ($this->country instanceof LazyLoadingProxy) {
            $this->country = $this->country->_loadRealInstance();
        }
        return $this->country;
    }
}
