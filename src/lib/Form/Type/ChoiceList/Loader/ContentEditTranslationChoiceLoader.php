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

final class ContentEditTranslationChoiceLoader extends BaseChoiceLoader
{
    /**
     * @param string[] $languageCodes
     */
    public function __construct(
        private readonly LanguageService $languageService,
        private readonly PermissionResolver $permissionResolver,
        private readonly LookupLimitationsTransformer $lookupLimitationsTransformer,
        private readonly array $languageCodes,
        private readonly LocationService $locationService,
        private readonly ?ContentInfo $contentInfo = null,
        private readonly ?Location $location = null
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getChoiceList(): array
    {
        $languages = iterator_to_array($this->languageService->loadLanguages());
        $limitationLanguageCodes = [];

        if (!empty($this->languageCodes)) {
            $languages = array_filter(
                $languages,
                function (Language $language): bool {
                    return \in_array($language->languageCode, $this->languageCodes, true);
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
                            ? $this->location->getId()
                            : $this->contentInfo->getMainLocationId()
                    ),
                ],
                [Limitation::LANGUAGE]
            );

            $limitationLanguageCodes = $this->lookupLimitationsTransformer->getFlattenedLimitationsValues(
                $lookupLimitations
            );
        }

        if (!empty($limitationLanguageCodes)) {
            $languages = array_filter(
                $languages,
                static function (Language $language) use ($limitationLanguageCodes): bool {
                    return in_array($language->getLanguageCode(), $limitationLanguageCodes, true);
                }
            );
        }

        return $languages;
    }
}
