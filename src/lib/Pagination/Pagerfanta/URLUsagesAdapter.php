<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\URL\URL;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo>
 */
final readonly class URLUsagesAdapter implements AdapterInterface
{
    public function __construct(
        private URL $url,
        private URLService $urlService
    ) {
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
