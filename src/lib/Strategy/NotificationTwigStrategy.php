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
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

final class NotificationTwigStrategy
{
    private ?string $defaultTemplate = null;

    public function __construct(
        private readonly Repository $repository,
        private readonly ContentService $contentService
    ) {
    }

    public function setDefault(string $defaultTemplate): void
    {
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\NoValidResultException
     */
    public function decide(mixed $contentId): string
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
                function () use ($contentId): ContentInfo {
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
            function () use ($contentId): ContentInfo {
                return $this->contentService->loadContentInfo($contentId);
            }
        );

        return $contentInfo->isTrashed();
    }
}
