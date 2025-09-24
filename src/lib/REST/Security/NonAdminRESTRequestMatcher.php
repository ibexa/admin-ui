<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Security;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

final readonly class NonAdminRESTRequestMatcher implements RequestMatcherInterface
{
    /**
     * @param string[][] $siteAccessGroups
     */
    public function __construct(private array $siteAccessGroups)
    {
    }

    public function matches(Request $request): bool
    {
        return
            $request->attributes->get('is_rest_request') &&
            !$this->isAdminSiteAccess($request);
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy(
            $request->attributes->get('siteaccess')
        );
    }
}
