<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use function Ibexa\PolyfillPhp82\iterator_to_array;

class VersionsDataset
{
    protected ContentService $contentService;

    protected ValueFactory $valueFactory;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[] */
    protected $data;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\AdminUi\UI\Value\ValueFactory $valueFactory
     */
    public function __construct(ContentService $contentService, ValueFactory $valueFactory)
    {
        $this->contentService = $contentService;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return VersionsDataset
     */
    public function load(ContentInfo $contentInfo): self
    {
        $versions = $this->contentService->loadVersions($contentInfo);

        $this->data = array_map(
            [$this->valueFactory, 'createVersionInfo'],
            iterator_to_array($versions)
        );

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[]
     */
    public function getVersions(): array
    {
        return $this->data;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[]
     */
    public function getDraftVersions(): array
    {
        return $this->filterVersions(
            $this->data,
            static function (VersionInfo $versionInfo): bool {
                return $versionInfo->isDraft();
            }
        );
    }

    /**
     * @param int $currentVersionNo
     * @param string $languageCode
     *
     * @return array
     */
    public function getConflictedDraftVersions(int $currentVersionNo, string $languageCode): array
    {
        return $this->filterVersions(
            $this->data,
            static function (VersionInfo $versionInfo) use ($currentVersionNo, $languageCode): bool {
                return $versionInfo->isDraft()
                    && $versionInfo->versionNo > $currentVersionNo
                    && $versionInfo->initialLanguageCode === $languageCode;
            }
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[]
     */
    public function getPublishedVersions(): array
    {
        return $this->filterVersions(
            $this->data,
            static function (VersionInfo $versionInfo): bool {
                return $versionInfo->isPublished();
            }
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[]
     */
    public function getArchivedVersions(): array
    {
        return $this->filterVersions(
            $this->data,
            static function (VersionInfo $versionInfo): bool {
                return $versionInfo->isArchived();
            }
        );
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[] $versions
     * @param callable $callable
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[]
     */
    protected function filterVersions(array $versions, callable $callable): array
    {
        return array_values(array_filter($versions, $callable));
    }
}
