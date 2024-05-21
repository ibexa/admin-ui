<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Dashboard;

use Ibexa\AdminUi\Pagination\Mapper\AbstractPagerContentToDataMapper;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\Repository\LocationResolver\LocationResolver;
use Pagerfanta\Pagerfanta;

/**
 * @deprecated in favour of PagerLocationToDataMapper
 * @see \Ibexa\AdminUi\Tab\Dashboard\PagerLocationToDataMapper
 */
class PagerContentToDataMapper extends AbstractPagerContentToDataMapper
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    protected $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    protected $userService;

    /** @var \Ibexa\Core\Repository\LocationResolver\LocationResolver */
    protected $locationResolver;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider
     * @param \Ibexa\Core\Helper\TranslationHelper $translationHelper
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Ibexa\Core\Repository\LocationResolver\LocationResolver $locationResolver
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        UserService $userService,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        TranslationHelper $translationHelper,
        LanguageService $languageService,
        LocationResolver $locationResolver
    ) {
        @trigger_error(
            sprintf(
                'The "%s" class is deprecated. Use "%s" instead.',
                __CLASS__,
                'Ibexa\AdminUi\Tab\Dashboard\PagerLocationToDataMapper'
            ),
            E_USER_DEPRECATED
        );

        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->userService = $userService;
        $this->locationResolver = $locationResolver;

        parent::__construct(
            $contentTypeService,
            $userService,
            $userLanguagePreferenceProvider,
            $translationHelper,
            $languageService
        );
    }

    /**
     * @param \Pagerfanta\Pagerfanta $pager
     *
     * @return array
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function map(Pagerfanta $pager): array
    {
        $data = [];
        $contentTypeIds = [];

        foreach ($pager as $content) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
            $contentInfo = $content->contentInfo;

            $contentTypeIds[] = $contentInfo->contentTypeId;
            $data[] = [
                'content' => $content,
                'contentTypeId' => $contentInfo->contentTypeId,
                'contentId' => $content->id,
                'name' => $this->translationHelper->getTranslatedContentName($content),
                'language' => $contentInfo->mainLanguageCode,
                'contributor' => $this->getVersionContributor($content->versionInfo),
                'version' => $content->versionInfo->versionNo,
                'content_type' => $content->getContentType(),
                'modified' => $content->versionInfo->modificationDate,
                'initialLanguageCode' => $content->versionInfo->initialLanguageCode,
                'content_is_user' => $this->isContentIsUser($content),
                'available_enabled_translations' => $this->getAvailableTranslations($content, true),
                'resolvedLocation' => $this->locationResolver->resolveLocation($contentInfo),
            ];
        }

        $this->setTranslatedContentTypesNames($data, $contentTypeIds);

        return $data;
    }
}
