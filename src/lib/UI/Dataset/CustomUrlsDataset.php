<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias;

class CustomUrlsDataset
{
    /** @var \Ibexa\Contracts\Core\Repository\URLAliasService */
    private $urlAliasService;

    /** @var \Ibexa\AdminUi\UI\Value\ValueFactory */
    private $valueFactory;

    /** @var \Ibexa\AdminUi\UI\Value\Content\UrlAlias[] */
    private $data;

    /**
     * @param \Ibexa\Contracts\Core\Repository\URLAliasService $urlAliasService
     * @param \Ibexa\AdminUi\UI\Value\ValueFactory $valueFactory
     */
    public function __construct(
        URLAliasService $urlAliasService,
        ValueFactory $valueFactory
    ) {
        $this->urlAliasService = $urlAliasService;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Ibexa\AdminUi\UI\Dataset\CustomUrlsDataset
     */
    public function load(Location $location): self
    {
        $customUrlAliases = $this->urlAliasService->listLocationAliases($location, true, null, true);
        $this->data = array_map(
            function (URLAlias $urlAlias) {
                return $this->valueFactory->createUrlAlias($urlAlias);
            },
            $customUrlAliases
        );

        return $this;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\UrlAlias[]
     */
    public function getCustomUrlAliases(): array
    {
        return $this->data;
    }
}

class_alias(CustomUrlsDataset::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\CustomUrlsDataset');
