<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\URL\URL;
use Pagerfanta\Adapter\AdapterInterface;

class URLUsagesAdapter implements AdapterInterface
{
    /**
     * @var URLService
     */
    private $urlService;

    /**
     * @var URL
     */
    private $url;

    /**
     * @param URL $url
     * @param URLService $urlService
     */
    public function __construct(
        URL $url,
        URLService $urlService
    ) {
        $this->urlService = $urlService;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults(): int
    {
        return $this->urlService->findUsages($this->url, 0, 0)->totalCount;
    }

    /**
     * {@inheritdoc}
     *
     * @return ContentInfo[]
     */
    public function getSlice(
        $offset,
        $length
    ): array {
        return $this->urlService->findUsages($this->url, $offset, $length)->items;
    }
}

class_alias(URLUsagesAdapter::class, 'EzSystems\EzPlatformAdminUi\Pagination\Pagerfanta\URLUsagesAdapter');
