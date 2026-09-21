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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IsProfileAvailableTest extends TestCase
{
    #[DataProvider('dataProviderForIsSatisfiedBy')]
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
    public static function dataProviderForIsSatisfiedBy(): iterable
    {
        yield 'disabled' => [
            self::createConfiguration(false, ['editor']),
            self::createUser('editor'),
            false,
        ];

        yield 'invalid content type' => [
            self::createConfiguration(true, ['editor']),
            self::createUser('user'),
            false,
        ];

        yield 'available' => [
            self::createConfiguration(true, ['editor']),
            self::createUser('editor'),
            true,
        ];
    }

    /**
     * @param string[] $contentTypes
     */
    private static function createConfiguration(bool $enabled, array $contentTypes): UserProfileConfigurationInterface
    {
        $configuration = self::createStub(UserProfileConfigurationInterface::class);
        $configuration->method('isEnabled')->willReturn($enabled);
        $configuration->method('getContentTypes')->willReturn($contentTypes);

        return $configuration;
    }

    private static function createUser(string $contentTypeIdentifier): User
    {
        $contentType = self::createStub(ContentType::class);
        $contentType->method('getIdentifier')->willReturn($contentTypeIdentifier);

        $user = self::createStub(User::class);
        $user->method('getContentType')->willReturn($contentType);

        return $user;
    }
}
