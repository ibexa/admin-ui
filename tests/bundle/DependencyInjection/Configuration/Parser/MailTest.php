<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\Mail;
use Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension;
use Ibexa\Tests\Bundle\Core\DependencyInjection\Configuration\Parser\AbstractParserTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Mail::class)]
final class MailTest extends AbstractParserTestCase
{
    private const string GROUP = 'ibexa_demo_group';
    private const string SITE_ACCESS = 'ibexa_demo_site';
    private const string SITE_ACCESS_PARAMETER = 'ibexa.site_access.config.ibexa_demo_site.' . Mail::SUPPORT_EMAIL_PARAM;

    protected function getContainerExtensions(): array
    {
        return [
            new IbexaCoreExtension([new Mail()]),
        ];
    }

    public function testSupportEmailIsMapped(): void
    {
        $this->load([
            'system' => [
                self::SITE_ACCESS => [
                    'mail' => ['support_email' => 'helpdesk@example.com'],
                ],
            ],
        ]);

        $this->assertConfigResolverParameterValue(Mail::SUPPORT_EMAIL_PARAM, 'helpdesk@example.com', self::SITE_ACCESS);
    }

    public function testExplicitNullHidesTheSupportLine(): void
    {
        $this->load([
            'system' => [
                self::GROUP => [
                    'mail' => ['support_email' => 'helpdesk@example.com'],
                ],
                self::SITE_ACCESS => [
                    'mail' => ['support_email' => null],
                ],
            ],
        ]);

        self::assertTrue($this->container->hasParameter(self::SITE_ACCESS_PARAMETER));
        $this->assertConfigResolverParameterValue(Mail::SUPPORT_EMAIL_PARAM, null, self::SITE_ACCESS);
    }

    public function testEmptyMailNodeInheritsGroupSettings(): void
    {
        $this->load([
            'system' => [
                self::GROUP => [
                    'mail' => ['support_email' => 'helpdesk@example.com'],
                ],
                self::SITE_ACCESS => [
                    'mail' => [],
                ],
            ],
        ]);

        self::assertFalse($this->container->hasParameter(self::SITE_ACCESS_PARAMETER));
        $this->assertConfigResolverParameterValue(Mail::SUPPORT_EMAIL_PARAM, 'helpdesk@example.com', self::SITE_ACCESS);
    }
}
