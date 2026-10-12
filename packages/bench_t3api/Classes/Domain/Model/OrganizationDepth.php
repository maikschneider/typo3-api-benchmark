<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\T3api\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class OrganizationDepth extends AbstractEntity
{
    /**
     * @T3api\Serializer\Groups({"depth2", "depth3"})
     */
    protected string $name = '';

    /**
     * @T3api\Serializer\Groups({"depth3"})
     */
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
