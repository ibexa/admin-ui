<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Siteaccess;

use Ibexa\AdminUi\Siteaccess\AdminSiteaccessPreviewVoter;
use Ibexa\AdminUi\Siteaccess\SiteaccessPreviewVoterContext;
use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AdminSiteaccessPreviewVoterTest extends TestCase
{
    private const string LANGUAGE_CODE = 'eng-GB';

    private ConfigResolverInterface&MockObject $configResolver;

    private RepositoryConfigurationProviderInterface&MockObject $repositoryConfigurationProvider;

    private AdminSiteaccessPreviewVoter $adminSiteaccessPreviewVoter;

    public function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->repositoryConfigurationProvider = $this->createMock(RepositoryConfigurationProviderInterface::class);

        $this->adminSiteaccessPreviewVoter = new AdminSiteaccessPreviewVoter(
            $this->configResolver,
            $this->repositoryConfigurationProvider
        );
    }

    public function testVoteWithInvalidPath(): void
    {
        $languageCode = self::LANGUAGE_CODE;
        $location = new Location(['id' => 1234, 'path' => [1]]);
        $versionInfo = new VersionInfo([
            'contentInfo' => new ContentInfo(['mainLanguageCode' => $languageCode]),
        ]);
        $siteaccess = 'site';

        $context = new SiteaccessPreviewVoterContext($location, $versionInfo, $siteaccess, $languageCode);

        $this->mockConfigMethods($context, 3);

        self::assertFalse($this->adminSiteaccessPreviewVoter->vote($context));
    }

    /**
     * @dataProvider dataProviderForSiteaccessPreviewVoterContext
     */
    public function testVoteWithInvalidLanguageMatch(SiteaccessPreviewVoterContext $context): void
    {
        $this->mockConfigMethods($context, 5, null, ['ger-DE']);

        $this->repositoryConfigurationProvider
            ->expects(self::once())
            ->method('getDefaultRepositoryAlias')
            ->willReturn('default');

        $this->repositoryConfigurationProvider
            ->expects(self::once())
            ->method('getCurrentRepositoryAlias')
            ->willReturn('default');

        self::assertFalse($this->adminSiteaccessPreviewVoter->vote($context));
    }

    /**
     * @dataProvider dataProviderForSiteaccessPreviewVoterContext
     */
    public function testVoteWithInvalidRepositoryMatch(SiteaccessPreviewVoterContext $context): void
    {
        $this->mockConfigMethods($context, 4);

        $this->repositoryConfigurationProvider
            ->expects(self::once())
            ->method('getDefaultRepositoryAlias')
            ->willReturn('default');

        $this->repositoryConfigurationProvider
            ->expects(self::once())
            ->method('getCurrentRepositoryAlias')
            ->willReturn('main');

        self::assertFalse($this->adminSiteaccessPreviewVoter->vote($context));
    }

    /**
     * @dataProvider dataProviderForSiteaccessPreviewVoterContext
     */
    public function testVoteWithValidRepositoryAndLanguageMatch(SiteaccessPreviewVoterContext $context): void
    {
        $this->mockConfigMethods($context, 5, null, ['eng-GB', 'fre-FR']);

        $this->repositoryConfigurationProvider
            ->expects(self::once())
            ->method('getDefaultRepositoryAlias')
            ->willReturn('default');

        $this->repositoryConfigurationProvider
            ->expects(self::once())
            ->method('getCurrentRepositoryAlias')
            ->willReturn('default');

        self::assertTrue($this->adminSiteaccessPreviewVoter->vote($context));
    }

    /**
     * @param string[] $languages
     */
    private function mockConfigMethods(
        SiteaccessPreviewVoterContext $context,
        int $expectedCalls,
        ?string $repository = null,
        array $languages = []
    ): void {
        $this->configResolver
            ->expects(self::exactly($expectedCalls))
            ->method('getParameter')
            ->willReturnCallback(static function (
                string $parameterName,
                ?string $namespace = null,
                ?string $scope = null
            ) use ($context, $repository, $languages): mixed {
                self::assertNull($namespace);
                self::assertSame($context->getSiteaccess(), $scope);

                return match ($parameterName) {
                    'content.tree_root.location_id' => 2,
                    'location_ids.media' => 43,
                    'location_ids.users' => 5,
                    'repository' => $repository,
                    'languages' => $languages,
                    default => throw new RuntimeException(
                        sprintf('Unexpected config parameter requested: %s', $parameterName)
                    ),
                };
            });
    }

    /**
     * @return array<int, array{0: \Ibexa\AdminUi\Siteaccess\SiteaccessPreviewVoterContext}>
     */
    public function dataProviderForSiteaccessPreviewVoterContext(): array
    {
        $languageCode = self::LANGUAGE_CODE;
        $location = new Location(['id' => 123456, 'path' => [1, 2]]);
        $versionInfo = new VersionInfo([
            'contentInfo' => new ContentInfo(['mainLanguageCode' => $languageCode]),
        ]);
        $siteaccess = 'site';

        $context = new SiteaccessPreviewVoterContext($location, $versionInfo, $siteaccess, $languageCode);

        return [
            [
                $context,
            ],
        ];
    }
}
