<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification\UserProfile;

use Ibexa\AdminUi\Specification\UserProfile\IsProfileAvailable;
use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use PHPUnit\Framework\TestCase;

final class IsProfileAvailableTest extends TestCase
{
    /**
     * @dataProvider dataProviderForIsSatisfiedBy
     */
    public function testIsSatisfiedBy(
        UserProfileConfigurationInterface $configuration,
        User $value,
        bool $expectedResult
    ): void {
        self::assertEquals(
            $expectedResult,
            (new IsProfileAvailable($configuration))->isSatisfiedBy($value)
        );
    }

    /**
     * @return iterable<array{UserProfileConfigurationInterface, \Ibexa\Contracts\Core\Repository\Values\User\User, bool}>
     */
    public function dataProviderForIsSatisfiedBy(): iterable
    {
        yield 'disabled' => [
            $this->createConfiguration(false, ['editor']),
            $this->createUser('editor'),
            false,
        ];

        yield 'invalid content type' => [
            $this->createConfiguration(true, ['editor']),
            $this->createUser('user'),
            false,
        ];

        yield 'available' => [
            $this->createConfiguration(true, ['editor']),
            $this->createUser('editor'),
            true,
        ];
    }

    /**
     * @param string[] $contentTypes
     */
    private function createConfiguration(bool $enabled, array $contentTypes): UserProfileConfigurationInterface
    {
        $configuration = $this->createMock(UserProfileConfigurationInterface::class);
        $configuration->method('isEnabled')->willReturn($enabled);
        $configuration->method('getContentTypes')->willReturn($contentTypes);

        return $configuration;
    }

    private function createUser(string $contentTypeIdentifier): User
    {
        $contentType = $this->createMock(ContentType::class);
        $contentType->method('__get')->with('identifier')->willReturn($contentTypeIdentifier);

        $user = $this->createMock(User::class);
        $user->method('getContentType')->willReturn($contentType);

        return $user;
    }
}
