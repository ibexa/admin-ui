<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\Data;

use Ibexa\AdminUi\Form\Data\ContentTranslationData;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;

class ContentTranslationDataTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Form\Data\ContentTranslationData */
    private ContentTranslationData $contentTranslationData;

    protected function setUp(): void
    {
        $this->contentTranslationData = new ContentTranslationData();
    }

    public function testAddFieldData(): void
    {
        self::assertNull($this->contentTranslationData->fieldsData);

        $this->contentTranslationData->addFieldData(new FieldData([
            'fieldDefinition' => $this->getFieldDefinition(),
        ]));

        self::assertCount(1, $this->contentTranslationData->fieldsData);

        // Add another field with same identifier
        $this->contentTranslationData->addFieldData(new FieldData([
            'fieldDefinition' => $this->getFieldDefinition(),
        ]));
        self::assertCount(1, $this->contentTranslationData->fieldsData);

        // Add field with another identifier
        $this->contentTranslationData->addFieldData(new FieldData([
            'fieldDefinition' => $this->getFieldDefinition('another_identifier'),
        ]));
        self::assertCount(2, $this->contentTranslationData->fieldsData);
    }

    /**
     * @param string $identifier
     *
     * @return \Ibexa\Core\Repository\Values\ContentType\FieldDefinition
     */
    private function getFieldDefinition(string $identifier = 'identifier'): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => $identifier,
        ]);
    }
}
