<?php

declare(strict_types=1);

namespace Typo3ApiBenchmark\T3api\Domain\Model;

use SourceBroker\T3api\Annotation as T3api;
use TYPO3\CMS\Extbase\Attribute\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class AuthorDepth extends AbstractEntity
{
    /**
     * @T3api\Serializer\Groups({"depth1", "depth2", "depth3"})
     */
    protected string $name = '';

    /**
     * @T3api\Serializer\Groups({"depth2", "depth3"})
     */
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
