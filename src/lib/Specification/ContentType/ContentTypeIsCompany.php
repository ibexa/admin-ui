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

final class ContentTypeIsCompany extends AbstractSpecification
{
    private string $companyContentTypeIdentifier;

    public function __construct(string $companyContentTypeIdentifier)
    {
        $this->companyContentTypeIdentifier = $companyContentTypeIdentifier;
    }

    public function isSatisfiedBy($contentType): bool
    {
        if (!$contentType instanceof ContentType) {
            throw new InvalidArgumentException(
                '$contentType',
                sprintf('Must be an instance of %s', ContentType::class)
            );
        }

        return $contentType->identifier === $this->companyContentTypeIdentifier;
    }
}
