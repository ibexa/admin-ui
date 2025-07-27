<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\URL\URL;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo>
 */
class URLUsagesAdapter implements AdapterInterface
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\URLService
     */
    private URLService $urlService;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\URL\URL
     */
    private URL $url;

    public function __construct(URL $url, URLService $urlService)
    {
        $this->urlService = $urlService;
        $this->url = $url;
    }

    public function getNbResults(): int
    {
        /** @phpstan-var int<0, max> */
        return $this->urlService->findUsages($this->url, 0, 0)->totalCount;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[]
     */
    public function getSlice($offset, $length): array
    {
        return $this->urlService->findUsages($this->url, $offset, $length)->items;
    }
}
