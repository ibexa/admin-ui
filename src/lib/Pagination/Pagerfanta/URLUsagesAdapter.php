<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\URL\URL;
use Pagerfanta\Adapter\AdapterInterface;

class URLUsagesAdapter implements AdapterInterface
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\URLService
     */
    private $urlService;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\URL\URL
     */
    private $url;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\URL\URL $url
     * @param \Ibexa\Contracts\Core\Repository\URLService $urlService
     */
    public function __construct(URL $url, URLService $urlService)
    {
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
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[]
     */
    public function getSlice($offset, $length): array
    {
        return $this->urlService->findUsages($this->url, $offset, $length)->items;
    }
}

class_alias(URLUsagesAdapter::class, 'EzSystems\EzPlatformAdminUi\Pagination\Pagerfanta\URLUsagesAdapter');
