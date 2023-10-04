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
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LanguageLimitationController extends Controller
{
    private ContentService $contentService;

    private LimitationResolverInterface $limitationResolver;

    private LocationService $locationService;

    public function __construct(
        ContentService $contentService,
        LimitationResolverInterface $limitationResolver,
        LocationService $locationService
    ) {
        $this->contentService = $contentService;
        $this->limitationResolver = $limitationResolver;
        $this->locationService = $locationService;
    }

    public function loadLanguageLimitationsForContentCreateAction(Location $location): Response
    {
        $contentInfo = $location->getContentInfo();
        $contentType = $contentInfo->getContentType();
        $contentCreateStruct = $this->contentService->newContentCreateStruct(
            $contentType,
            $contentInfo->getMainLanguageCode()
        );
        $contentCreateStruct->sectionId = $contentInfo->getSection();
        $locationCreateStruct = $this->locationService->newLocationCreateStruct($location->id);

        return new JsonResponse(
            $this->limitationResolver->getLanguageLimitations(
                'create',
                $contentCreateStruct,
                [],
                [
                    $locationCreateStruct,
                ]
            )
        );
    }

    public function loadLanguageLimitationsForContentEditAction(
        ContentInfo $contentInfo,
        ?VersionInfo $versionInfo = null,
        ?Location $location = null
    ): Response {
        return new JsonResponse(
            $this->getLanguageLimitationsByFunction(
                'edit',
                $contentInfo,
                $versionInfo,
                $location
            )
        );
    }

    public function loadLanguageLimitationsForContentReadAction(
        ContentInfo $contentInfo,
        ?VersionInfo $versionInfo = null,
        ?Location $location = null
    ): Response {
        return new JsonResponse(
            $this->getLanguageLimitationsByFunction(
                'read',
                $contentInfo,
                $versionInfo,
                $location
            )
        );
    }

    /**
     * @return array<array{
     *     languageCode: string,
     *     name: string,
     *     hasAccess: bool,
     * }>
     */
    private function getLanguageLimitationsByFunction(
        string $function,
        ContentInfo $contentInfo,
        ?VersionInfo $versionInfo = null,
        ?Location $location = null
    ): array {
        $versionInfo ??= $this->contentService->loadVersionInfo($contentInfo);
        $location ??= $contentInfo->getMainLocation();
        $targets = [];

        if (null !== $location) {
            $targets[] = $location;
        }

        return $this->limitationResolver->getLanguageLimitations(
            $function,
            $contentInfo,
            $versionInfo->getLanguages(),
            $targets
        );
    }
}
