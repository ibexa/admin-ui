<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Content;

use Ibexa\AdminUi\Specification\Content\ContentDraftHasConflict;
use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\UserService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class VersionDraftConflictController extends Controller
{
    private LocationService $locationService;

    private ContentService $contentService;

    private DatasetFactory $datasetFactory;

    private UserService $userService;

    private TranslatorInterface $translator;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\AdminUi\UI\Dataset\DatasetFactory $datasetFactory
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(
        LocationService $locationService,
        ContentService $contentService,
        DatasetFactory $datasetFactory,
        UserService $userService,
        TranslatorInterface $translator
    ) {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->datasetFactory = $datasetFactory;
        $this->userService = $userService;
        $this->translator = $translator;
    }

    /**
     * @param int $contentId
     * @param string $languageCode
     * @param int|null $locationId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function draftHasNoConflictAction(
        int $contentId,
        string $languageCode,
        ?int $locationId = null
    ): Response {
        $content = $this->contentService->loadContent($contentId);
        $contentInfo = $content->contentInfo;

        try {
            $contentDraftHasConflict = (new ContentDraftHasConflict($this->contentService, $languageCode))->isSatisfiedBy($contentInfo);
        } catch (UnauthorizedException $e) {
            $error = $this->translator->trans(
                /** @Desc("Cannot check if the draft has conflicts with other drafts. %error%.") */
                'content.draft.conflict.error',
                ['%error%' => $e->getMessage()],
                'ibexa_content'
            );

            return new Response($error, Response::HTTP_FORBIDDEN);
        }

        if ($contentDraftHasConflict) {
            $versionsDataset = $this->datasetFactory->versions();
            $versionsDataset->load($contentInfo);
            $conflictedDrafts = $versionsDataset->getConflictedDraftVersions($contentInfo->currentVersionNo, $languageCode);
            $locationId = $locationId ?? $contentInfo->mainLocationId;
            try {
                $location = $this->locationService->loadLocation($locationId);
            } catch (UnauthorizedException $e) {
                // Will return list of locations user has *read* access to, or empty array if none
                $availableLocations = $this->locationService->loadLocations($contentInfo);
                // will return null if array of availableLocations is empty
                $location = array_shift($availableLocations);
            }

            $modalContent = $this->renderView('@ibexadesign/content/modal/draft_conflict.html.twig', [
                'conflicted_drafts' => $conflictedDrafts,
                'location' => $location,
                'content_is_user' => (new ContentIsUser($this->userService))->isSatisfiedBy($content),
            ]);

            return new Response($modalContent, Response::HTTP_CONFLICT);
        }

        return new Response();
    }
}
