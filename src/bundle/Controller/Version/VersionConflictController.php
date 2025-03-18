<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Version;

use Ibexa\AdminUi\Specification\Version\VersionHasConflict;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Core\Base\Exceptions\BadStateException;
use Symfony\Component\HttpFoundation\Response;

class VersionConflictController extends Controller
{
    private ContentService $contentService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Checks if Version has conflict with another published Version.
     *
     * If Version has no conflict, return empty Response. If it has conflict return HTML with content of modal.
     *
     * @param int $contentId
     * @param int $versionNo
     * @param string $languageCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Core\Base\Exceptions\BadStateException
     */
    public function versionHasNoConflictAction(int $contentId, int $versionNo, string $languageCode): Response
    {
        $versionInfo = $this->contentService->loadVersionInfoById($contentId, $versionNo);

        if (!$versionInfo->isDraft()) {
            throw new BadStateException('Version status', 'the status is not draft');
        }

        if ((new VersionHasConflict($this->contentService, $languageCode))->isSatisfiedBy($versionInfo)) {
            return new Response('', Response::HTTP_CONFLICT);
        }

        return new Response();
    }
}
