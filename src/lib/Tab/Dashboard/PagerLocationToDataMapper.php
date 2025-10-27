<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\LocationResolver\LocationResolver;
use Pagerfanta\Pagerfanta;

final class PagerLocationToDataMapper
{
    /** @var UserService */
    private $userService;

    /** @var LocationResolver */
    private $locationResolver;

    private LanguageService $languageService;

    public function __construct(
        UserService $userService,
        LocationResolver $locationResolver,
        LanguageService $languageService
    ) {
        $this->userService = $userService;
        $this->locationResolver = $locationResolver;
        $this->languageService = $languageService;
    }

    /**
     * @param Pagerfanta<Location> $pager
     *
     * @return array<
     *      array{
     *          'contentTypeId': int,
     *          'contentId': int,
     *          'name': string,
     *          'type': ?string,
     *          'language': string,
     *          'available_enabled_translations': Language[],
     *          'contributor': ?User,
     *          'content_type': ContentType,
     *          'modified': \DateTime,
     *          'resolvedLocation': Location
     *      }
     * >
     *
     * @throws ForbiddenException
     * @throws BadStateException
     * @throws NotFoundException
     */
    public function map(
        Pagerfanta $pager,
        bool $doMapVersionInfoData = false
    ): array {
        $data = [];

        /** @var Location $location */
        foreach ($pager as $location) {
            $contentInfo = $location->getContentInfo();
            $versionInfo = $doMapVersionInfoData ? $location->getContent()->getVersionInfo() : null;
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
     * @return Language[]
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
