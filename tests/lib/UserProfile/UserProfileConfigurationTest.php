<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UserProfile;

use Ibexa\AdminUi\UserProfile\UserProfileConfiguration;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

final class UserProfileConfigurationTest extends TestCase
{
    private const EXAMPLE_FIELD_GROUPS = ['about', 'contact'];
    private const EXAMPLE_CONTENT_TYPES = ['editor'];

    private UserProfileConfiguration $configuration;

    protected function setUp(): void
    {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->method('getParameter')
            ->willReturnMap([
                ['user_profile.enabled', null, null, true],
                ['user_profile.field_groups', null, null, self::EXAMPLE_FIELD_GROUPS],
                ['user_profile.content_types', null, null, self::EXAMPLE_CONTENT_TYPES],
            ]);

        $this->configuration = new UserProfileConfiguration($configResolver);
    }

    public function testIsEnabled(): void
    {
        self::assertTrue($this->configuration->isEnabled());
    }

    public function testGetFieldGroups(): void
    {
        self::assertEquals(self::EXAMPLE_FIELD_GROUPS, $this->configuration->getFieldGroups());
    }

    public function testGetContentTypes(): void
    {
        self::assertEquals(self::EXAMPLE_CONTENT_TYPES, $this->configuration->getContentTypes());
    }
}
