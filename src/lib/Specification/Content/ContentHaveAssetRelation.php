<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Content;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationType;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class ContentHaveAssetRelation extends AbstractSpecification
{
    public function __construct(private readonly ContentService $contentService)
    {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function isSatisfiedBy(mixed $item): bool
    {
        if (!$item instanceof Content) {
            throw new InvalidArgumentException(
                $item,
                sprintf('Must be an instance of %s', Content::class)
            );
        }

        return $this->contentService->countRelations(
            $item->getVersionInfo(),
            RelationType::ASSET
        ) > 0;
    }
}
