<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification;

use Ibexa\AdminUi\Specification\Version\VersionHasConflict;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\TestCase;

class VersionHasConflictTest extends TestCase
{
    public function testVersionWithStatusDraft(): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject $contentServiceMock */
        $contentServiceMock = $this->createMock(ContentService::class);
        $contentServiceMock
            ->method('loadVersions')
            ->willReturn([
                $this->createVersionInfo(false),
                $this->createVersionInfo(false, 2),
                $this->createVersionInfo(false, 3),
                $this->createVersionInfo(true, 4),
            ]);

        $versionHasConflict = new VersionHasConflict($contentServiceMock, 'eng-GB');

        self::assertFalse($versionHasConflict->isSatisfiedBy($this->createVersionInfo(false, 5)));
    }

    public function testVersionWithStatusDraftAndVersionConflict(): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject $contentServiceMock */
        $contentServiceMock = $this->createMock(ContentService::class);
        $contentServiceMock
            ->method('loadVersions')
            ->willReturn([
                $this->createVersionInfo(false),
                $this->createVersionInfo(false, 3),
                $this->createVersionInfo(true, 4),
            ]);

        $versionHasConflict = new VersionHasConflict($contentServiceMock, 'eng-GB');

        self::assertTrue($versionHasConflict->isSatisfiedBy($this->createVersionInfo(false, 2)));
    }

    public function testVersionWithStatusDraftAndVersionConflictWithAnotherLanguageCode(): void
    {
        $contentServiceMock = $this->createMock(ContentService::class);
        $contentServiceMock
            ->method('loadVersions')
            ->willReturn([
                $this->createVersionInfo(false, 1, 'pol-PL'),
                $this->createVersionInfo(false, 3, 'pol-PL'),
                $this->createVersionInfo(true, 4, 'pol-PL'),
            ]);

        $versionHasConflict = new VersionHasConflict($contentServiceMock, 'eng-GB');

        self::assertFalse($versionHasConflict->isSatisfiedBy($this->createVersionInfo(false, 2, 'eng-GB')));
    }

    /**
     * Returns VersionInfo.
     *
     * @param bool $isPublished
     * @param int $versionNo
     * @param string $languageCode
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo
     */
    private function createVersionInfo(bool $isPublished = false, int $versionNo = 1, string $languageCode = 'eng-GB'): VersionInfo
    {
        $contentInfo = $this->createMock(ContentInfo::class);

        $versionInfo = $this->getMockForAbstractClass(
            VersionInfo::class,
            [],
            '',
            true,
            true,
            true,
            ['isPublished', '__get', 'getContentInfo']
        );

        $versionInfo
            ->method('isPublished')
            ->willReturn($isPublished);

        $versionInfo
            ->method('__get')
            ->willReturnMap(
                [
                    ['initialLanguageCode', $languageCode],
                    ['versionNo', $versionNo],
                ]
            );

        $versionInfo
            ->method('getContentInfo')
            ->willReturn($contentInfo);

        return $versionInfo;
    }
}
