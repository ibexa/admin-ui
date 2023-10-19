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
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

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
        $contentType = $contentInfo->getContentType();
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
        string $function,
        ValueObject $valueObject,
        iterable $languages = [],
        array $targets = []
    ): array {
        $languages = !empty($languages) ? $languages : $this->languageService->loadLanguages();
        $versionBuilder = new VersionBuilder();
        $versionBuilder->translateToAnyLanguageOf($this->getActiveLanguageCodes($languages));

        $lookupLimitations = $this->permissionResolver->lookupLimitations(
            'content',
            $function,
            $valueObject,
            array_merge(
                $targets,
                [$versionBuilder->build()]
            ),
            [Limitation::LANGUAGE]
        );

        $limitationLanguageCodes = $this->lookupLimitationsTransformer->getFlattenedLimitationsValues($lookupLimitations);
        $languageLimitations = [];
        foreach ($languages as $language) {
            $languageLimitations[] = [
                'languageCode' => $language->getLanguageCode(),
                'name' => $language->getName(),
                'hasAccess' => $lookupLimitations->hasAccess && $this->hasAccessToLanguage($language, $limitationLanguageCodes),
            ];
        }

        return $languageLimitations;
    }

    /**
     * @param array<string> $limitationLanguageCodes
     */
    private function hasAccessToLanguage(Language $language, array $limitationLanguageCodes): bool
    {
        return $language->isEnabled()
            && (
                empty($limitationLanguageCodes)
                || in_array($language->getLanguageCode(), $limitationLanguageCodes, true)
            );
    }

    /**
     * @return array<int>
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
     * @param iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Language>|null $languages
     *
     * @return array<string>
     */
    private function getActiveLanguageCodes(?iterable $languages = null): array
    {
        $languages ??= $this->languageService->loadLanguages();
        $languageCodes = [];
        foreach ($languages as $language) {
            if ($language->isEnabled()) {
                $languageCodes[] = $language->getLanguageCode();
            }
        }

        return $languageCodes;
    }
}
