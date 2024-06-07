<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

abstract class AbstractSiteaccessPreviewVoter implements SiteaccessPreviewVoterInterface
{
    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    protected $configResolver;

    /** @var \Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider */
    protected $repositoryConfigurationProvider;

    public function __construct(
        ConfigResolverInterface $configResolver,
        RepositoryConfigurationProvider $repositoryConfigurationProvider
    ) {
        $this->configResolver = $configResolver;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(SiteaccessPreviewVoterContext $context): bool
    {
        $siteAccess = $context->getSiteaccess();
        $location = $context->getLocation();
        $languageCode = $context->getLanguageCode();

        if (empty(array_intersect($this->getRootLocationIds($siteAccess), $location->getPath()))) {
            return false;
        }

        if (!$this->validateRepositoryMatch($siteAccess)) {
            return false;
        }

        $siteAccessLanguages = $this->configResolver->getParameter(
            'languages',
            null,
            $siteAccess
        );

        return in_array($languageCode, $siteAccessLanguages, true);
    }

    protected function validateRepositoryMatch(string $siteaccess): bool
    {
        $siteaccessRepository = $this->configResolver->getParameter(
            'repository',
            null,
            $siteaccess
        );

        // If SA does not have a repository configured we should obtain the default one, which is used as a fallback.
        $siteaccessRepository = $siteaccessRepository ?: $this->repositoryConfigurationProvider->getDefaultRepositoryAlias();
        $currentRepository = $this->repositoryConfigurationProvider->getCurrentRepositoryAlias();

        return $siteaccessRepository === $currentRepository;
    }

    /**
     * @param string $siteaccess
     *
     * @return int[]
     */
    abstract protected function getRootLocationIds(string $siteaccess): array;
}

class_alias(AbstractSiteaccessPreviewVoter::class, 'EzSystems\EzPlatformAdminUi\Siteaccess\AbstractSiteaccessPreviewVoter');
