<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\ContentType;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class ContentTypeIsUserGroup extends AbstractSpecification
{
    /**
     * @param string[] $userGroupContentTypeIdentifiers
     */
    public function __construct(private readonly array $userGroupContentTypeIdentifiers)
    {
    }

    /**
     * Checks if $contentType is an existing User content.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function isSatisfiedBy(mixed $contentType): bool
    {
        if (!$contentType instanceof ContentType) {
            throw new InvalidArgumentException(
                $contentType,
                sprintf('Must be an instance of %s', ContentType::class)
            );
        }

        return in_array(
            $contentType->getIdentifier(),
            $this->userGroupContentTypeIdentifiers,
            true
        );
    }
}
