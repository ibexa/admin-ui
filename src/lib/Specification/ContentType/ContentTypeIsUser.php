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

class ContentTypeIsUser extends AbstractSpecification
{
    private const EZUSER_FIELD_TYPE_IDENTIFIER = 'ibexa_user';

    private array $userContentTypeIdentifier;

    /**
     * @param array $userContentTypeIdentifier
     */
    public function __construct(array $userContentTypeIdentifier)
    {
        $this->userContentTypeIdentifier = $userContentTypeIdentifier;
    }

    /**
     * Checks if $contentType is an existing User content.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return bool
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function isSatisfiedBy($contentType): bool
    {
        if (!$contentType instanceof ContentType) {
            throw new InvalidArgumentException('$contentType', sprintf('Must be an instance of %s', ContentType::class));
        }

        if (in_array($contentType->identifier, $this->userContentTypeIdentifier, true)) {
            return true;
        }

        return $contentType->hasFieldDefinitionOfType(self::EZUSER_FIELD_TYPE_IDENTIFIER);
    }
}
