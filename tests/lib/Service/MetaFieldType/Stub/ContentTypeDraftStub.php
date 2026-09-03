<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Service\MetaFieldType\Stub;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCollection;

/**
 * Minimal concrete {@see ContentTypeDraft} double used in unit tests to allow reading the
 * `fieldDefinitions` magic property without pulling in the full Repository object graph.
 */
final class ContentTypeDraftStub extends ContentTypeDraft
{
    private FieldDefinitionCollection $fieldDefinitionCollection;

    public function __construct(FieldDefinitionCollection $fieldDefinitionCollection)
    {
        parent::__construct();

        $this->fieldDefinitionCollection = $fieldDefinitionCollection;
    }

    public function __get($property)
    {
        if ('fieldDefinitions' === $property) {
            return $this->getFieldDefinitions();
        }

        return parent::__get($property);
    }

    public function getFieldDefinitions(): FieldDefinitionCollection
    {
        return $this->fieldDefinitionCollection;
    }

    /**
     * @return array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup>
     */
    public function getContentTypeGroups(): array
    {
        return [];
    }

    public function getNames(): array
    {
        return [];
    }

    public function getName($languageCode = null): ?string
    {
        return null;
    }

    public function getDescriptions(): array
    {
        return [];
    }

    public function getDescription($languageCode = null): ?string
    {
        return null;
    }
}
