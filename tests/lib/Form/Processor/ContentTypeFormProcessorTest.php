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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Form;
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
            ->expects(self::once())
            ->method('getData')
            ->willReturn($fieldTypeIdentifier);
        $mainForm = $this->createMock(FormInterface::class);
        $mainForm
            ->expects(self::once())
            ->method('get')
            ->with('fieldTypeSelection')
            ->willReturn($fieldTypeSelectionForm);

        $formConfig = $this->createMock(FormConfigInterface::class);
        $formConfig
            ->method('getOption')
            ->with('languageCode')
            ->willReturn($languageCode);

        $mainForm
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('loadContentTypeDraft')
            ->with($contentTypeDraft->id)
            ->willReturn($contentTypeDraft);
        $this->contentTypeService
            ->expects(self::once())
            ->method('addFieldDefinition')
            ->with($contentTypeDraft, self::equalTo($expectedFieldDefCreateStruct));
        $this->groupsList
            ->expects(self::once())
            ->method('getDefaultGroup')
            ->will(self::returnValue('content'));

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
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('publishContentTypeDraft')
            ->with($contentTypeDraft);

        $this->router
            ->expects(self::once())
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
        $fieldDefinition1 = new FieldDefinition(['identifier' => 'field_1']);
        $fieldDefinition2 = new FieldDefinition(['identifier' => 'field_2']);
        $fieldDefinition3 = new FieldDefinition(['identifier' => 'field_3']);
        $existingFieldDefinitions = [$fieldDefinition1, $fieldDefinition2, $fieldDefinition3];
        $contentTypeDraft = new ContentTypeDraft([
            'innerContentType' => new ContentType([
                'fieldDefinitions' => $existingFieldDefinitions,
                'identifier' => 'foo',
            ]),
        ]);

        $compoundFormConfig = $this->createMock(FormConfigInterface::class);
        $compoundFormConfig->method('getCompound')->willReturn(true);
        $compoundFormConfig->method('getDataMapper')->willReturn($this->createMock(DataMapperInterface::class));
        $fieldDefinitionsDataForm = new Form($compoundFormConfig);
        $fieldDefinitionsDataForm->add($this->mockFieldDefinitionForm($fieldDefinition1, false));
        $fieldDefinitionsDataForm->add($this->mockFieldDefinitionForm($fieldDefinition2, true));
        $fieldDefinitionsDataForm->add($this->mockFieldDefinitionForm($fieldDefinition3, true));

        $mainForm = $this->createMock(FormInterface::class);
        $mainForm
            ->expects(self::once())
            ->method('get')
            ->with('fieldDefinitionsData')
            ->willReturn($fieldDefinitionsDataForm);

        // only 2 fields are selected for removal: field 2 and 3
        $matcher = self::exactly(2);
        $this->contentTypeService->expects($matcher)
                                 ->method('removeFieldDefinition')
            // replacement for deprecated withConsecutive method
                                 ->willReturnCallback(
                                     static function (
                                         ContentTypeDraft $actualContentTypeDraft,
                                         FieldDefinition $actualFieldDefinition
                                     ) use ($matcher, $contentTypeDraft, $fieldDefinition2, $fieldDefinition3) {
                                        self::assertSame($contentTypeDraft, $actualContentTypeDraft);
                                        match ($matcher->getInvocationCount()) {
                                            1 => self::assertSame($fieldDefinition2, $actualFieldDefinition),
                                            2 => self::assertSame($fieldDefinition3, $actualFieldDefinition),
                                            default => self::fail('Unexpected invocation count matched'),
                                        };
                                    }
                                 )
        ;

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
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('deleteContentType')
            ->with($contentTypeDraft);

        $this->router
            ->expects(self::once())
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

    private function mockFieldDefinitionForm(FieldDefinition $fieldDefinition, bool $isSelected): FormInterface & MockObject
    {
        $fieldDefinitionForm = $this->createMock(FormInterface::class);
        $fieldDefinitionForm->method('getName')->willReturn(uniqid('child', true));
        $fieldDefinitionSelectedForm = $this->createMock(FormInterface::class);
        $fieldDefinitionForm
            ->expects(self::once())
            ->method('get')
            ->with('selected')
            ->willReturn($fieldDefinitionSelectedForm)
        ;
        $fieldDefinitionSelectedForm
            ->expects(self::once())
            ->method('getData')
            ->willReturn($isSelected)
        ;
        $fieldDefinitionForm
            ->expects($isSelected ? self::once() : self::never())
            ->method('getData')
            ->willReturn(new FieldDefinitionData(['fieldDefinition' => $fieldDefinition]))
        ;

        return $fieldDefinitionForm;
    }
}
