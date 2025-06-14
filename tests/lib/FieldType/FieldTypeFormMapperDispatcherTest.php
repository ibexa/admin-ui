<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\FieldType;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\FieldType\FieldTypeDefinitionFormMapperDispatcher;
use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Core\FieldType\FieldTypeAliasResolverInterface;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

class FieldTypeFormMapperDispatcherTest extends TestCase
{
    private FieldTypeDefinitionFormMapperDispatcher $dispatcher;

    private FieldDefinitionFormMapperInterface&MockObject $fieldDefinitionMapperMock;

    private FieldTypeAliasResolverInterface&MockObject $fieldTypeAliasResolver;

    protected function setUp(): void
    {
        $this->fieldDefinitionMapperMock = $this->createMock(FieldDefinitionFormMapperInterface::class);
        $this->fieldTypeAliasResolver = $this->createMock(FieldTypeAliasResolverInterface::class);

        $this->dispatcher = new FieldTypeDefinitionFormMapperDispatcher($this->fieldTypeAliasResolver);
        $this->dispatcher->addMapper($this->fieldDefinitionMapperMock, 'first_type');
    }

    public function testMapFieldDefinition(): void
    {
        $data = new FieldDefinitionData([
            'fieldDefinition' => new FieldDefinition(['fieldTypeIdentifier' => 'first_type']),
            'contentTypeData' => new ContentTypeData([
                'contentTypeDraft' => new ContentTypeDraft([
                    'innerContentType' => new ContentType([
                        'identifier' => 'foo',
                    ]),
                ]),
            ]),
        ]);

        $formMock = $this->createMock(FormInterface::class);

        $this->fieldTypeAliasResolver
            ->method('resolveIdentifier')
            ->willReturnArgument(0);

        $this->fieldDefinitionMapperMock
            ->expects(self::once())
            ->method('mapFieldDefinitionForm')
            ->with($formMock, $data);

        $this->dispatcher->map($formMock, $data);
    }
}
