<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\ContentType;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Specification\AbstractSpecification;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

class ContentTypeIsDashboardContainer extends AbstractSpecification
{
    /** @var string */
    private $dashboardGroupContentTypeIdentifier;

    /**
     * @param string $dashboardGroupContentTypeIdentifier
     */
    public function __construct(string $dashboardGroupContentTypeIdentifier)
    {
        $this->dashboardGroupContentTypeIdentifier = $dashboardGroupContentTypeIdentifier;
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
            throw new InvalidArgumentException($contentType, sprintf('Must be an instance of %s', ContentType::class));
        }
        dump($this->dashboardGroupContentTypeIdentifier);

        return $contentType->identifier === $this->dashboardGroupContentTypeIdentifier;
    }
}

class_alias(ContentTypeIsDashboardContainer::class, 'EzSystems\EzPlatformAdminUi\Specification\ContentType\ContentTypeIsDashboardContainer');
