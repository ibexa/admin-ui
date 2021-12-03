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

abstract class AbstractPagerContentToDataMapper
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var \Ibexa\Core\Helper\TranslationHelper */
    protected $translationHelper;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    public function __construct(
        ContentTypeService $contentTypeService,
        UserService $userService,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        TranslationHelper $translationHelper,
        LanguageService $languageService
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->userService = $userService;
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
        $this->translationHelper = $translationHelper;
        $this->languageService = $languageService;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param bool $filterDisabled
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
     */
    protected function getAvailableTranslations(
        Content $content,
        bool $filterDisabled = false
    ): iterable {
        $availableTranslationsLanguages = $this->languageService->loadLanguageListByCode(
            $content->versionInfo->languageCodes
        );

        if (false === $filterDisabled) {
            return $availableTranslationsLanguages;
        }

        return array_filter(
            $availableTranslationsLanguages,
            (static function (Language $language): bool {
                return $language->enabled;
            })
        );
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return bool
     */
    protected function isContentIsUser(Content $content): bool
    {
        return (new ContentIsUser($this->userService))->isSatisfiedBy($content);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User|null
     */
    protected function getVersionContributor(VersionInfo $versionInfo): ?User
    {
        try {
            return $this->userService->loadUser($versionInfo->creatorId);
        } catch (NotFoundException $e) {
            return null;
        }
    }

    /**
     * @param array $data
     * @param int[] $contentTypeIds
     */
    protected function setTranslatedContentTypesNames(array &$data, array $contentTypeIds): void
    {
        // load list of Content Types with proper translated names
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

class_alias(AbstractPagerContentToDataMapper::class, 'EzSystems\EzPlatformAdminUi\Pagination\Mapper\AbstractPagerContentToDataMapper');
