<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission\ContextProvider;

use Ibexa\Contracts\AdminUi\Permission\PermissionCheckContextProviderInterface;
use Ibexa\Contracts\AdminUi\Values\PermissionCheckContext;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class ContentItemContextProvider implements PermissionCheckContextProviderInterface
{
    private const POLICY_MODULE_CONTENT = 'content';

    private ContentService $contentService;

    private LocationService $locationService;

    /** @var array<string> */
    private array $userContentTypeIdentifiers;

    /**
     * @param array<string> $userContentTypeIdentifiers
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        array $userContentTypeIdentifiers
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->userContentTypeIdentifiers = $userContentTypeIdentifiers;
    }

    public function supports(string $module, string $function): bool
    {
        return self::POLICY_MODULE_CONTENT === $module;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getPermissionCheckContext(
        string $module,
        string $function,
        Request $request
    ): PermissionCheckContext {
        $query = $request->query;

        $contentInfo = $this->getContentInfo($query);
        $targets = $this->getTargets($query);
        $criteria = $this->createCriteria();

        return new PermissionCheckContext($contentInfo, $targets, $criteria);
    }

    private function getContentInfo(ParameterBag $query): ContentInfo
    {
        $contentId = $query->getInt('contentId');

        return $this->contentService->loadContentInfo($contentId);
    }

    /**
     * @return array<\Ibexa\Contracts\Core\Repository\Values\ValueObject>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Exception\InvalidArgumentException
     */
    private function getTargets(ParameterBag $query): array
    {
        if (!$query->has('locationId')) {
            return [];
        }

        $locationId = $query->getInt('locationId');
        if ($locationId <= 0) {
            throw new InvalidArgumentException(
                'locationId',
                'Expected value should be greater than 0.'
            );
        }

        $location = $this->locationService->loadLocation($locationId);

        return [$location];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    private function createCriteria(): CriterionInterface
    {
        $criteria = [new Criterion\ContentTypeIdentifier($this->userContentTypeIdentifiers)];

        return new Criterion\LogicalAnd($criteria);
    }
}
