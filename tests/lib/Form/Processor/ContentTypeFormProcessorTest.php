<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\Processor;

use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Form\Processor\ContentType\ContentTypeFormProcessor;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Event\FormEvents;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * @covers \Ibexa\AdminUi\Form\Processor\ContentType\ContentTypeFormProcessor
 */
final class ContentTypeFormProcessorTest extends TestCase
{
    private const EXAMPLE_CONTENT_TYPE_ID = 1;

    /**
     * @var \Ibexa\Contracts\Core\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contentTypeService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Ibexa\AdminUi\Form\Processor\ContentType\ContentTypeFormProcessor
     */
    private $formProcessor;

    /**
     * @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList|\PHPUnit\Framework\MockObject\MockObject
     */
    private $groupsList;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->groupsList = $this->createMock(FieldsGroupsList::class);

        $this->formProcessor = new ContentTypeFormProcessor(
            $this->contentTypeService,
            $this->router
        );
        $this->formProcessor->setGroupsList($this->groupsList);
    }

    public function testSubscribedEvents(): void
    {
        self::assertSame([
            FormEvents::CONTENT_TYPE_UPDATE => 'processDefaultAction',
            FormEvents::CONTENT_TYPE_ADD_FIELD_DEFINITION => 'processAddFieldDefinition',
            FormEvents::CONTENT_TYPE_REMOVE_FIELD_DEFINITION => 'processRemoveFieldDefinition',
            FormEvents::CONTENT_TYPE_PUBLISH => 'processPublishContentType',
            FormEvents::CONTENT_TYPE_PUBLISH_AND_EDIT => 'processPublishAndEditContentType',
            FormEvents::CONTENT_TYPE_REMOVE_DRAFT => 'processRemoveContentTypeDraft',
        ], ContentTypeFormProcessor::getSubscribedEvents());
    }

    public function testProcessDefaultAction(): void
    {
        $contentTypeDraft = $this->getContentTypeDraft();
        $fieldDef1 = new FieldDefinition();
        $fieldDefData1 = new FieldDefinitionData([
            'fieldDefinition' => $fieldDef1,
            'fieldGroup' => 'foo',
            'identifier' => 'foo',
        ]);
        $fieldDef2 = new FieldDefinition();
        $fieldDefData2 = new FieldDefinitionData([
            'fieldDefinition' => $fieldDef2,
            'fieldGroup' => 'foo',
            'identifier' => 'bar',
        ]);
        $contentTypeData = new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]);
        $contentTypeData->addFieldDefinitionData($fieldDefData1);
        $contentTypeData->addFieldDefinitionData($fieldDefData2);

        $this->contentTypeService
            ->expects(self::exactly(2))
            ->method('updateFieldDefinition')
            ->withConsecutive(
                [$contentTypeDraft, $fieldDef1, $fieldDefData1],
                [$contentTypeDraft, $fieldDef2, $fieldDefData2],
            );
        $this->contentTypeService
            ->expects(self::once())
            ->method('updateContentTypeDraft')
            ->with($contentTypeDraft, $contentTypeData);

        $event = new FormActionEvent($this->createMock(FormInterface::class), $contentTypeData, 'fooAction');
        $this->formProcessor->processDefaultAction($event);
    }

    public function testAddFieldDefinition(): void
    {
        $fieldTypeIdentifier = 'ezstring';
        $languageCode = 'fre-FR';
        $existingFieldDefinitions = new FieldDefinitionCollection([
            new FieldDefinition([
                'fieldTypeIdentifier' => $fieldTypeIdentifier,
                'identifier' => sprintf('new_%s_%d', $fieldTypeIdentifier, 1),
            ]),
            new FieldDefinition([
                'fieldTypeIdentifier' => $fieldTypeIdentifier,
                'identifier' => sprintf('new_%s_%d', $fieldTypeIdentifier, 2),
            ]),
        ]);
        $contentTypeDraft = new ContentTypeDraft([
            'innerContentType' => new ContentType([
                'id' => self::EXAMPLE_CONTENT_TYPE_ID,
                'identifier' => 'foo',
                'fieldDefinitions' => $existingFieldDefinitions,
                'mainLanguageCode' => $languageCode,
            ]),
        ]);
        $expectedNewFieldDefIdentifier = sprintf(
            'new_%s_%d',
            $fieldTypeIdentifier,
            \count($existingFieldDefinitions) + 1
        );

        $fieldTypeSelectionForm = $this->createMock(FormInterface::class);
        $fieldTypeSelectionForm
            ->expects($this->once())
            ->method('getData')
            ->willReturn($fieldTypeIdentifier);
        $mainForm = $this->createMock(FormInterface::class);
        $mainForm
            ->expects($this->once())
            ->method('get')
            ->with('fieldTypeSelection')
            ->willReturn($fieldTypeSelectionForm);

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig
            ->method('getOption')
            ->with('languageCode')
            ->willReturn($languageCode);

        $mainForm
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn($formConfig);

        $expectedFieldDefCreateStruct = new FieldDefinitionCreateStruct([
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
            'identifier' => $expectedNewFieldDefIdentifier,
            'names' => [$languageCode => 'New FieldDefinition'],
            'position' => 1,
            'fieldGroup' => 'content',
        ]);
        $this->contentTypeService
            ->expects($this->once())
            ->method('loadContentTypeDraft')
            ->with($contentTypeDraft->id)
            ->willReturn($contentTypeDraft);
        $this->contentTypeService
            ->expects($this->once())
            ->method('addFieldDefinition')
            ->with($contentTypeDraft, $this->equalTo($expectedFieldDefCreateStruct));
        $this->groupsList
            ->expects($this->once())
            ->method('getDefaultGroup')
            ->will($this->returnValue('content'));

        $event = new FormActionEvent(
            $mainForm,
            new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]),
            'addFieldDefinition',
            ['languageCode' => $languageCode]
        );

        $this->formProcessor->processAddFieldDefinition($event);
    }

    public function testPublishContentType(): void
    {
        $contentTypeDraft = $this->getContentTypeDraft();
        $event = new FormActionEvent(
            $this->createMock(FormInterface::class),
            new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]),
            'publishContentType',
            ['languageCode' => 'eng-GB']
        );
        $this->contentTypeService
            ->expects($this->once())
            ->method('publishContentTypeDraft')
            ->with($contentTypeDraft);

        $this->formProcessor->processPublishContentType($event);
    }

    public function testPublishContentTypeWithRedirection(): void
    {
        $redirectRoute = 'foo';
        $redirectUrl = 'http://foo.com/bar';
        $contentTypeDraft = $this->getContentTypeDraft();
        $event = new FormActionEvent(
            $this->createMock(FormInterface::class),
            new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]),
            'publishContentType',
            ['languageCode' => 'eng-GB']
        );
        $this->contentTypeService
            ->expects($this->once())
            ->method('publishContentTypeDraft')
            ->with($contentTypeDraft);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with($redirectRoute)
            ->willReturn($redirectUrl);
        $expectedRedirectResponse = new RedirectResponse($redirectUrl);
        $formProcessor = new ContentTypeFormProcessor(
            $this->contentTypeService,
            $this->router,
            ['redirectRouteAfterPublish' => $redirectRoute]
        );
        $formProcessor->processPublishContentType($event);
        self::assertTrue($event->hasResponse());
        self::assertEquals($expectedRedirectResponse, $event->getResponse());
    }

    public function testRemoveFieldDefinition(): void
    {
        $fieldDefinition1 = new FieldDefinition();
        $fieldDefinition2 = new FieldDefinition();
        $fieldDefinition3 = new FieldDefinition();
        $existingFieldDefinitions = [$fieldDefinition1, $fieldDefinition2, $fieldDefinition3];
        $contentTypeDraft = new ContentTypeDraft([
            'innerContentType' => new ContentType([
                'fieldDefinitions' => $existingFieldDefinitions,
                'identifier' => 'foo',
            ]),
        ]);

        $fieldDefForm1 = $this->createMock(FormInterface::class);
        $fieldDefSelected1 = $this->createMock(FormInterface::class);
        $fieldDefForm1
            ->expects($this->once())
            ->method('get')
            ->with('selected')
            ->willReturn($fieldDefSelected1);
        $fieldDefSelected1
            ->expects($this->once())
            ->method('getData')
            ->willReturn(false);
        $fieldDefForm1
            ->expects($this->never())
            ->method('getData');

        $fieldDefForm2 = $this->createMock(FormInterface::class);
        $fieldDefSelected2 = $this->createMock(FormInterface::class);
        $fieldDefForm2
            ->expects($this->once())
            ->method('get')
            ->with('selected')
            ->willReturn($fieldDefSelected2);
        $fieldDefSelected2
            ->expects($this->once())
            ->method('getData')
            ->willReturn(true);
        $fieldDefForm2
            ->expects($this->once())
            ->method('getData')
            ->willReturn(new FieldDefinitionData(['fieldDefinition' => $fieldDefinition1]));

        $fieldDefForm3 = $this->createMock(FormInterface::class);
        $fieldDefSelected3 = $this->createMock(FormInterface::class);
        $fieldDefForm3
            ->expects($this->once())
            ->method('get')
            ->with('selected')
            ->willReturn($fieldDefSelected3);
        $fieldDefSelected3
            ->expects($this->once())
            ->method('getData')
            ->willReturn(true);
        $fieldDefForm3
            ->expects($this->once())
            ->method('getData')
            ->willReturn(new FieldDefinitionData(['fieldDefinition' => $fieldDefinition1]));

        $mainForm = $this->createMock(FormInterface::class);
        $mainForm
            ->expects($this->once())
            ->method('get')
            ->with('fieldDefinitionsData')
            ->willReturn([$fieldDefForm1, $fieldDefForm2, $fieldDefForm3]);

        $event = new FormActionEvent(
            $mainForm,
            new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]),
            'removeFieldDefinition',
            ['languageCode' => 'eng-GB']
        );
        $this->formProcessor->processRemoveFieldDefinition($event);
    }

    public function testRemoveContentTypeDraft(): void
    {
        $contentTypeDraft = $this->getContentTypeDraft();
        $event = new FormActionEvent(
            $this->createMock(FormInterface::class),
            new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]),
            'removeDraft',
            ['languageCode' => 'eng-GB']
        );
        $this->contentTypeService
            ->expects($this->once())
            ->method('deleteContentType')
            ->with($contentTypeDraft);

        $this->formProcessor->processRemoveContentTypeDraft($event);
    }

    public function testRemoveContentTypeDraftWithRedirection(): void
    {
        $redirectRoute = 'foo';
        $redirectUrl = 'http://foo.com/bar';
        $contentTypeDraft = $this->getContentTypeDraft();
        $event = new FormActionEvent(
            $this->createMock(FormInterface::class),
            new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]),
            'removeDraft',
            ['languageCode' => 'eng-GB']
        );
        $this->contentTypeService
            ->expects($this->once())
            ->method('deleteContentType')
            ->with($contentTypeDraft);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with($redirectRoute)
            ->willReturn($redirectUrl);
        $expectedRedirectResponse = new RedirectResponse($redirectUrl);
        $formProcessor = new ContentTypeFormProcessor(
            $this->contentTypeService,
            $this->router,
            ['redirectRouteAfterPublish' => $redirectRoute]
        );
        $formProcessor->processRemoveContentTypeDraft($event);
        self::assertTrue($event->hasResponse());
        self::assertEquals($expectedRedirectResponse, $event->getResponse());
    }

    private function getContentTypeDraft(): ContentTypeDraft
    {
        return new ContentTypeDraft([
            'innerContentType' => new ContentType([
                'identifier' => 'foo',
            ]),
        ]);
    }
}

class_alias(ContentTypeFormProcessorTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\Processor\ContentTypeFormProcessorTest');
