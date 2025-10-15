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
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class VersionDraftConflictController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService,
        private readonly ContentService $contentService,
        private readonly DatasetFactory $datasetFactory,
        private readonly UserService $userService,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
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
        $contentInfo = $content->getContentInfo();

        try {
            $contentDraftHasConflict = (
                new ContentDraftHasConflict($this->contentService, $languageCode)
            )->isSatisfiedBy($contentInfo);
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
            $conflictedDrafts = $versionsDataset->getConflictedDraftVersions(
                $contentInfo->currentVersionNo,
                $languageCode
            );

            $locationId = $locationId ?? $contentInfo->getMainLocationId();
            if ($locationId === null) {
                throw new RuntimeException(
                    'Location ID is required to load the version draft conflict modal.'
                );
            }

            try {
                $location = $this->locationService->loadLocation($locationId);
            } catch (UnauthorizedException) {
                // Will return list of locations user has *read* access to, or empty array if none
                $availableLocations = iterator_to_array(
                    $this->locationService->loadLocations($contentInfo)
                );
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
