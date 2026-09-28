<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\REST\Security;

use Ibexa\AdminUi\REST\Security\NonAdminRESTRequestMatcher;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class NonAdminRESTRequestMatcherTest extends TestCase
{
    public function testMatchRESTRequestInAdminContext(): void
    {
        $adminRESTRequestMatcher = new NonAdminRESTRequestMatcher(
            [
                'admin_group' => [
                    'admin',
                ],
            ]
        );

        $request = new Request(attributes: [
            'is_rest_request' => true,
            'siteaccess' => new SiteAccess('admin'),
        ]);

        self::assertFalse($adminRESTRequestMatcher->matches($request));
    }

    public function testMatchNonRESTRequest(): void
    {
        $adminRESTRequestMatcher = new NonAdminRESTRequestMatcher([]);

        $request = new Request(attributes: [
            'is_rest_request' => false,
        ]);

        self::assertFalse($adminRESTRequestMatcher->matches($request));
    }

    public function testMatchRESTRequestNotInAdminContext(): void
    {
        $adminRESTRequestMatcher = new NonAdminRESTRequestMatcher(
            [
                'admin_group' => [
                    'admin',
                ],
                'another_group' => [
                    'ibexa',
                ],
            ]
        );

        $request = new Request(attributes: [
            'is_rest_request' => true,
            'siteaccess' => new SiteAccess('ibexa'),
        ]);

        self::assertTrue($adminRESTRequestMatcher->matches($request));
    }
}
