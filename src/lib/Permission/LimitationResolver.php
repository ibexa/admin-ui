<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\Contracts\Core\Limitation\Target\Builder\VersionBuilder;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;

/**
 * @internal
 */
final class LimitationResolver implements LimitationResolverInterface
{
    private ContentService $contentService;

    private ContentTypeService $contentTypeService;

    private LanguageService $languageService;

    private LocationService $locationService;

    private LookupLimitationsTransformer $lookupLimitationsTransformer;

    private PermissionResolver $permissionResolver;

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LanguageService $languageService,
        LocationService $locationService,
        LookupLimitationsTransformer $lookupLimitationsTransformer,
        PermissionResolver $permissionResolver
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->languageService = $languageService;
        $this->locationService = $locationService;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
        $this->permissionResolver = $permissionResolver;
    }

    public function getContentCreateLimitations(Location $parentLocation): LookupLimitationResult
    {
        $contentInfo = $parentLocation->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($contentInfo->getContentType()->id);
        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $contentInfo->getMainLanguageCode());
        $contentCreateStruct->sectionId = $contentInfo->getSection();
        $locationCreateStruct = $this->locationService->newLocationCreateStruct($parentLocation->id);

        $versionBuilder = new VersionBuilder();
        $versionBuilder->translateToAnyLanguageOf($this->getActiveLanguageCodes());
        $versionBuilder->createFromAnyContentTypeOf($this->getContentTypeIds());

        return $this->permissionResolver->lookupLimitations(
            'content',
            'create',
            $contentCreateStruct,
            [$versionBuilder->build(), $locationCreateStruct],
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );
    }

    public function getContentUpdateLimitations(Location $parentLocation): LookupLimitationResult
    {
        $versionBuilder = new VersionBuilder();
        $versionBuilder->translateToAnyLanguageOf($this->getActiveLanguageCodes());
        $versionBuilder->createFromAnyContentTypeOf($this->getContentTypeIds());

        return $this->permissionResolver->lookupLimitations(
            'content',
            'edit',
            $parentLocation->getContentInfo(),
            [$versionBuilder->build(), $parentLocation],
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );
    }

    public function getLanguageLimitations(
        VersionInfo $versionInfo,
        Location $location
    ): array {
        $languages = $versionInfo->getLanguages();
        $lookupLimitations = $this->permissionResolver->lookupLimitations(
            'content',
            'edit',
            $versionInfo->getContentInfo(),
            [
                (new VersionBuilder())->translateToAnyLanguageOf($this->getActiveLanguageCodes($languages))->build(),
                $location,
            ],
            [Limitation::LANGUAGE]
        );

        $limitationLanguageCodes = $this->lookupLimitationsTransformer->getFlattenedLimitationsValues($lookupLimitations);

        return array_map(
            static function (Language $language) use ($limitationLanguageCodes): array {
                return [
                    'languageCode' => $language->getLanguageCode(),
                    'name' => $language->getName(),
                    'hasAccess' => empty($limitationLanguageCodes) || in_array($language->getLanguageCode(), $limitationLanguageCodes, true),
                ];
            },
            $languages
        );
    }

    /**
     * @return array<string>
     */
    private function getContentTypeIds(): array
    {
        $contentTypeIds = [];

        $contentTypeGroups = $this->contentTypeService->loadContentTypeGroups();
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup);
            foreach ($contentTypes as $contentType) {
                $contentTypeIds[] = $contentType->id;
            }
        }

        return $contentTypeIds;
    }

    /**
     * @return array<string>
     */
    private function getActiveLanguageCodes(?array $languageCodes = null): array
    {
        $filter = array_filter(
            $languageCodes ?? $this->languageService->loadLanguages(),
            static function (Language $language) {
                return $language->enabled;
            }
        );

        return array_column($filter, 'languageCode');
    }
}
