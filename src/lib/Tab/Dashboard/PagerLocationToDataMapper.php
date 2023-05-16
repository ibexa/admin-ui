<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\LocationResolver\LocationResolver;
use Pagerfanta\Pagerfanta;

final class PagerLocationToDataMapper
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\Core\Repository\LocationResolver\LocationResolver */
    private $locationResolver;

    private LanguageService $languageService;

    public function __construct(
        ContentService $contentService,
        UserService $userService,
        LocationResolver $locationResolver,
        LanguageService $languageService
    ) {
        $this->contentService = $contentService;
        $this->userService = $userService;
        $this->locationResolver = $locationResolver;
        $this->languageService = $languageService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function map(Pagerfanta $pager, bool $doMapVersionInfoData = false): array
    {
        $data = [];

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        foreach ($pager as $location) {
            $contentInfo = $location->contentInfo;
            $versionInfo = $doMapVersionInfoData ? $this->contentService->loadVersionInfo($contentInfo) : null;
            $contentType = $location->getContentInfo()->getContentType();

            $data[] = [
                'contentTypeId' => $contentInfo->contentTypeId,
                'contentId' => $contentInfo->id,
                'name' => $contentInfo->name,
                'type' => $contentType->getName(),
                'language' => $contentInfo->mainLanguageCode,
                'available_enabled_translations' => $versionInfo !== null ? $this->getAvailableTranslations($versionInfo) : [],
                'contributor' => $versionInfo !== null ? $this->getVersionContributor($versionInfo) : null,
                'content_type' => $contentType,
                'modified' => $contentInfo->modificationDate,
                'resolvedLocation' => $this->locationResolver->resolveLocation($contentInfo),
            ];
        }

        return $data;
    }

    private function getVersionContributor(VersionInfo $versionInfo): ?User
    {
        try {
            return $this->userService->loadUser($versionInfo->creatorId);
        } catch (NotFoundException $e) {
            return null;
        }
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Language[]
     */
    private function getAvailableTranslations(
        VersionInfo $versionInfo
    ): array {
        $availableTranslationsLanguages = $this->languageService->loadLanguageListByCode(
            $versionInfo->languageCodes
        );

        return array_filter(
            $availableTranslationsLanguages,
            static function (Language $language): bool {
                return $language->enabled;
            }
        );
    }
}
