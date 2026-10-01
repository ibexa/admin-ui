<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Siteaccess;

/**
 * Display information of a SiteAccess: a human-readable name and, when the SiteAccess is reachable
 * under a known address, its domain (host, optionally followed by a path).
 */
final readonly class SiteAccessInfo
{
    public function __construct(
        private string $name,
        private ?string $domain = null
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }
}
