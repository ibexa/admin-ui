<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Strategy;

use Ibexa\AdminUi\Exception\NoValidResultException;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;

class NotificationTwigStrategy
{
    private Repository $repository;

    private ContentService $contentService;

    private ?string $defaultTemplate = null;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Repository $repository
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     */
    public function __construct(
        Repository $repository,
        ContentService $contentService
    ) {
        $this->repository = $repository;
        $this->contentService = $contentService;
    }

    /**
     * @param string $defaultTemplate
     */
    public function setDefault(string $defaultTemplate): void
    {
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * @param mixed $contentId
     *
     * @return string
     *
     * @throws \Ibexa\AdminUi\Exception\NoValidResultException
     */
    public function decide($contentId): string
    {
        $contentId = (int)$contentId;

        if ($this->isContentPermanentlyDeleted($contentId)) {
            return '@ibexadesign/account/notifications/list_item_deleted.html.twig';
        }
        if ($this->isContentTrashed($contentId)) {
            return '@ibexadesign/account/notifications/list_item_trashed.html.twig';
        }
        if (!empty($this->defaultTemplate)) {
            return $this->defaultTemplate;
        }

        throw new NoValidResultException();
    }

    private function isContentPermanentlyDeleted(int $contentId): bool
    {
        // Using sudo in order to be sure that information is valid in case user no longer have access to content i.e when in trash.
        try {
            $this->repository->sudo(
                function () use ($contentId) {
                    return $this->contentService->loadContentInfo($contentId);
                }
            );

            return false;
        } catch (NotFoundException $exception) {
            return true;
        }
    }

    private function isContentTrashed(int $contentId): bool
    {
        // Using sudo in order to be sure that information is valid in case user no longer have access to content i.e when in trash.
        $contentInfo = $this->repository->sudo(
            function () use ($contentId) {
                return $this->contentService->loadContentInfo($contentId);
            }
        );

        return $contentInfo->isTrashed();
    }
}
