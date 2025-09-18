<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\AdminUi\PreviewUrlResolver\VersionPreviewUrlResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;

final class VersionPreviewUrlChoiceLoader extends BaseChoiceLoader
{
    private SiteAccessServiceInterface $siteAccessService;

    private VersionPreviewUrlResolverInterface $previewUrlResolver;

    private SiteAccessChoiceLoader $siteAccessChoiceLoader;

    private VersionInfo $versionInfo;

    private Location $location;

    private Language $language;

    public function __construct(
        SiteAccessServiceInterface $siteAccessService,
        VersionPreviewUrlResolverInterface $previewUrlResolver,
        SiteAccessChoiceLoader $siteAccessChoiceLoader,
        VersionInfo $versionInfo,
        Location $location,
        Language $language
    ) {
        $this->siteAccessService = $siteAccessService;
        $this->previewUrlResolver = $previewUrlResolver;
        $this->siteAccessChoiceLoader = $siteAccessChoiceLoader;
        $this->versionInfo = $versionInfo;
        $this->location = $location;
        $this->language = $language;
    }

    /**
     * @return array<string, string> An associative array where keys are site access names and values are preview URLs.
     */
    public function getChoiceList(): array
    {
        $baseChoiceList = $this->siteAccessChoiceLoader->getChoiceList();

        $choiceList = [];
        foreach ($baseChoiceList as $siteAccessName => $siteAccessKey) {
            $choiceList[$siteAccessName] = $this->previewUrlResolver->resolveUrl(
                $this->versionInfo,
                $this->location,
                $this->language,
                $this->siteAccessService->get($siteAccessKey)
            );
        }

        return $choiceList;
    }
}
