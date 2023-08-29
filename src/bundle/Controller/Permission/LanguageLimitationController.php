<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Permission;

use Ibexa\AdminUi\Permission\LimitationResolverInterface;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LanguageLimitationController extends Controller
{
    private ContentService $contentService;

    private LocationService $locationService;

    private LimitationResolverInterface $limitationResolver;

    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        LimitationResolverInterface $limitationResolver
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->limitationResolver = $limitationResolver;
    }

    public function loadLanguageLimitationsForContentAction(
        int $contentId,
        ?int $versionNo = null,
        ?int $locationId = null
    ): Response {
        $versionInfo = $this->contentService->loadVersionInfoById($contentId, $versionNo);

        if (null === $locationId) {
            $locationId = $versionInfo->getContentInfo()->getMainLocationId();
        }

        return new JsonResponse(
            $this->limitationResolver->getLanguageLimitations(
                $versionInfo,
                $this->locationService->loadLocation($locationId)
            )
        );
    }
}
