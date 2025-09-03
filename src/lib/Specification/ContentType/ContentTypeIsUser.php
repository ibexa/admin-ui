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

final class ContentTypeIsUser extends AbstractSpecification
{
    private const string IBEXA_USER_FIELD_TYPE_IDENTIFIER = 'ibexa_user';

    /**
     * @param string[] $userContentTypeIdentifiers
     */
    public function __construct(private readonly array $userContentTypeIdentifiers)
    {
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function isSatisfiedBy(mixed $contentType): bool
    {
        if (!$contentType instanceof ContentType) {
            throw new InvalidArgumentException(
                '$contentType',
                sprintf('Must be an instance of %s', ContentType::class)
            );
        }

        if (in_array($contentType->getIdentifier(), $this->userContentTypeIdentifiers, true)) {
            return true;
        }

        return $contentType->hasFieldDefinitionOfType(self::IBEXA_USER_FIELD_TYPE_IDENTIFIER);
    }
}
