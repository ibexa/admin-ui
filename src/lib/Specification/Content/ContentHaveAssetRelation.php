<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Content;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Relation;
use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Specification\AbstractSpecification;

class ContentHaveAssetRelation extends AbstractSpecification
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * @param $item
     *
     * @return bool
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function isSatisfiedBy($item): bool
    {
        if (!$item instanceof Content) {
            throw new InvalidArgumentException($item, sprintf('Must be an instance of %s', Content::class));
        }

        $relations = $this->contentService->loadRelations($item->versionInfo);

        foreach ($relations as $relation) {
            if (Relation::ASSET === $relation->type) {
                return true;
            }
        }

        return false;
    }
}

class_alias(ContentHaveAssetRelation::class, 'EzSystems\EzPlatformAdminUi\Specification\Content\ContentHaveAssetRelation');
