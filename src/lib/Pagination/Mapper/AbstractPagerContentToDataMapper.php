<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Mapper;

use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;

abstract readonly class AbstractPagerContentToDataMapper
{
    public function __construct(
        private ContentTypeService $contentTypeService,
        private UserService $userService,
        private UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        protected TranslationHelper $translationHelper,
        private LanguageService $languageService
    ) {
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    protected function getAvailableTranslations(
        Content $content,
        bool $filterDisabled = false
    ): iterable {
        $availableTranslationsLanguages = $this->languageService->loadLanguageListByCode(
            $content->getVersionInfo()->getLanguageCodes()
        );

        if (false === $filterDisabled) {
            return $availableTranslationsLanguages;
        }

        return array_filter(
            iterator_to_array($availableTranslationsLanguages),
            static function (Language $language): bool {
                return $language->isEnabled();
            }
        );
    }

    protected function isContentIsUser(Content $content): bool
    {
        return (new ContentIsUser($this->userService))->isSatisfiedBy($content);
    }

    protected function getVersionContributor(VersionInfo $versionInfo): ?User
    {
        try {
            return $this->userService->loadUser($versionInfo->creatorId);
        } catch (NotFoundException) {
            return null;
        }
    }

    /**
     * @param array<int, mixed> $data
     * @param int[] $contentTypeIds
     */
    protected function setTranslatedContentTypesNames(array &$data, array $contentTypeIds): void
    {
        // load list of content types with proper translated names
        $contentTypes = $this->contentTypeService->loadContentTypeList(
            array_unique($contentTypeIds),
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );

        foreach ($data as $idx => $item) {
            // get content type from bulk-loaded list or fallback to lazy loaded one if not present
            $contentTypeId = $item['contentTypeId'];
            /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
            $contentType = $contentTypes[$contentTypeId] ?? $item['content']->getContentType();

            $data[$idx]['type'] = $contentType->getName();
            unset($data[$idx]['content'], $data[$idx]['contentTypeId']);
        }
    }
}
