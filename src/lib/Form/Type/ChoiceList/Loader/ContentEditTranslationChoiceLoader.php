<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use function in_array;

class ContentEditTranslationChoiceLoader extends BaseChoiceLoader
{
    private LanguageService $languageService;

    private PermissionResolver $permissionResolver;

    /** @var string[] */
    private array $languageCodes;

    private ?ContentInfo $contentInfo;

    private LookupLimitationsTransformer $lookupLimitationsTransformer;

    private LocationService $locationService;

    private ?Location $location;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     * @param \Ibexa\AdminUi\Permission\LookupLimitationsTransformer $lookupLimitationsTransformer
     * @param string[] $languageCodes
     */
    public function __construct(
        LanguageService $languageService,
        PermissionResolver $permissionResolver,
        ?ContentInfo $contentInfo,
        LookupLimitationsTransformer $lookupLimitationsTransformer,
        array $languageCodes,
        LocationService $locationService,
        ?Location $location
    ) {
        $this->languageService = $languageService;
        $this->permissionResolver = $permissionResolver;
        $this->contentInfo = $contentInfo;
        $this->languageCodes = $languageCodes;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
        $this->locationService = $locationService;
        $this->location = $location;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoiceList(): array
    {
        $languages = $this->languageService->loadLanguages();
        $limitationLanguageCodes = [];

        if (!empty($this->languageCodes)) {
            $languages = array_filter(
                $languages,
                function (Language $language): bool {
                    return in_array($language->languageCode, $this->languageCodes, true);
                }
            );
        }

        $languagesCodes = array_column($languages, 'languageCode');
        if (null !== $this->contentInfo) {
            $lookupLimitations = $this->permissionResolver->lookupLimitations(
                'content',
                'edit',
                $this->contentInfo,
                [
                    (new Target\Builder\VersionBuilder())->translateToAnyLanguageOf($languagesCodes)->build(),
                    $this->locationService->loadLocation(
                        $this->location !== null
                            ? $this->location->id
                            : $this->contentInfo->mainLocationId
                    ),
                ],
                [Limitation::LANGUAGE]
            );

            $limitationLanguageCodes = $this->lookupLimitationsTransformer->getFlattenedLimitationsValues($lookupLimitations);
        }

        if (!empty($limitationLanguageCodes)) {
            $languages = array_filter(
                $languages,
                static function (Language $language) use ($limitationLanguageCodes): bool {
                    return in_array($language->languageCode, $limitationLanguageCodes, true);
                }
            );
        }

        return $languages;
    }
}
