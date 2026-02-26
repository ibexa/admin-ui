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
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class NonAdminRESTRequestMatcherTest extends TestCase
{
    public function testMatchRESTRequestInAdminContext(): void
    {
        $siteAccessMock = $this->createMock(SiteAccess::class);
        $siteAccessMock->name = 'admin';
        $adminRESTRequestMatcher = new NonAdminRESTRequestMatcher(
            [
                'admin_group' => [
                    'admin',
                ],
            ]
        );

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);

        $request->attributes
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(static function (string $attribute) use ($siteAccessMock): mixed {
                self::assertContains($attribute, ['is_rest_request', 'siteaccess']);

                if ($attribute === 'is_rest_request') {
                    return true;
                }

                return $siteAccessMock;
            });

        self::assertFalse($adminRESTRequestMatcher->matches($request));
    }

    public function testMatchNonRESTRequest(): void
    {
        $adminRESTRequestMatcher = new NonAdminRESTRequestMatcher([]);

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);

        $request->attributes
            ->expects(self::once())
            ->method('get')
            ->with('is_rest_request')
            ->willReturn(false);

        self::assertFalse($adminRESTRequestMatcher->matches($request));
    }

    public function testMatchRESTRequestNotInAdminContext(): void
    {
        $siteAccessMock = $this->createMock(SiteAccess::class);
        $siteAccessMock->name = 'admin';
        $nonAdminSiteAccessMock = $this->createMock(SiteAccess::class);
        $nonAdminSiteAccessMock->name = 'ibexa';
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

        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);

        $request->attributes
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(static function (string $attribute) use ($nonAdminSiteAccessMock): mixed {
                self::assertContains($attribute, ['is_rest_request', 'siteaccess']);

                if ($attribute === 'is_rest_request') {
                    return true;
                }

                return $nonAdminSiteAccessMock;
            });

        self::assertTrue($adminRESTRequestMatcher->matches($request));
    }
}
