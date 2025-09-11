<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\LocationResolver\LocationResolver;
use Pagerfanta\Pagerfanta;

final readonly class PagerLocationToDataMapper
{
    public function __construct(
        private UserService $userService,
        private LocationResolver $locationResolver,
        private LanguageService $languageService
    ) {
    }

    /**
     * @param \Pagerfanta\Pagerfanta<\Ibexa\Contracts\Core\Repository\Values\Content\Location> $pager
     *
     * @return array<
     *      array{
     *          'contentTypeId': int,
     *          'contentId': int,
     *          'name': string,
     *          'type': ?string,
     *          'language': string,
     *          'available_enabled_translations': \Ibexa\Contracts\Core\Repository\Values\Content\Language[],
     *          'contributor': ?\Ibexa\Contracts\Core\Repository\Values\User\User,
     *          'content_type': \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType,
     *          'modified': \DateTime,
     *          'resolvedLocation': \Ibexa\Contracts\Core\Repository\Values\Content\Location
     *      }
     * >
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function map(Pagerfanta $pager, bool $doMapVersionInfoData = false): array
    {
        $data = [];

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        foreach ($pager as $location) {
            $contentInfo = $location->getContentInfo();
            $versionInfo = $doMapVersionInfoData ? $location->getContent()->getVersionInfo() : null;
            $contentType = $location->getContentInfo()->getContentType();

            $data[] = [
                'contentTypeId' => $contentInfo->contentTypeId,
                'contentId' => $contentInfo->getId(),
                'name' => $contentInfo->getName(),
                'type' => $contentType->getName(),
                'language' => $contentInfo->getMainLanguageCode(),
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
            return $this->userService->loadUser($versionInfo->getCreator()->getId());
        } catch (NotFoundException) {
            return null;
        }
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    private function getAvailableTranslations(
        VersionInfo $versionInfo
    ): array {
        $availableTranslationsLanguages = $this->languageService->loadLanguageListByCode(
            $versionInfo->getLanguageCodes()
        );

        return array_filter(
            $availableTranslationsLanguages,
            static function (Language $language): bool {
                return $language->isEnabled();
            }
        );
    }
}
