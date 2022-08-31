<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\AdminUi\Form\Data\FormMapper;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Form\Data\FormMapper\ContentTypeDraftMapper;
use Ibexa\Contracts\AdminUi\Event\FieldDefinitionMappingEvent;
use Ibexa\Contracts\AdminUi\Form\Data\FormMapper\FormDataMapperInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\FieldType\Value;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Ibexa\AdminUi\Form\Data\FormMapper\ContentTypeDraftMapper
 */
final class ContentTypeDraftMapperTest extends TestCase
{
    private FormDataMapperInterface $contentTypeDraftMapper;

    /** @var \Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeService $contentTypeService;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private EventDispatcherInterface $eventDispatcher;

    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList|\PHPUnit\Framework\MockObject\MockObject */
    private FieldsGroupsList $fieldsGroupsList;

    protected function setUp(): void
    {
        $this->contentTypeFieldTypesResolver = $this->createMock(ContentTypeFieldTypesResolverInterface::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->fieldGroupList = $this->createMock(FieldsGroupsList::class);

        $this->contentTypeDraftMapper = new ContentTypeDraftMapper(
            $this->contentTypeFieldTypesResolver,
            $this->contentTypeService,
            $this->eventDispatcher,
            $this->fieldGroupList
        );
    }

    public function testMapToFormData(): void
    {
        $fieldDefs = $this->createFieldDefinitions();
        $contentTypeDraft = $this->createContentTypeDraft($fieldDefs);
        $expectedContentTypeData = $this->createContentTypeData($contentTypeDraft);
        $fieldDefinitionsData = $this->createFieldDefinitionsData($fieldDefs, $expectedContentTypeData);

        foreach ($fieldDefinitionsData as $fieldDefinitionData) {
            $expectedContentTypeData->addFieldDefinitionData($fieldDefinitionData);
        }

        $this->mockContentTypeFieldTypesResolverGetFieldTypes();
        $this->mockEventDispatcherDispatch();
        $this->mockFieldGroupListGetDefaultGroup();

        self::assertEquals(
            $expectedContentTypeData,
            $this->contentTypeDraftMapper->mapToFormData($contentTypeDraft)
        );
    }

    /**
     * @return array<\Ibexa\Core\Repository\Values\ContentType\FieldDefinition>
     */
    private function createFieldDefinitions(): array
    {
        $fieldDefinitions = [];
        $fieldDefinitionsConfig = [
            'ezstring' => [
                'identifier' => 'identifier1',
                'defaultValue' => $this->getMockForAbstractClass(Value::class),
                'name' => 'Foo',
                'position' => 0,
            ],
            'eztext' => [
                'identifier' => 'identifier2',
                'defaultValue' => $this->getMockForAbstractClass(Value::class),
                'name' => 'Bar',
                'position' => 2,
            ],
            'ezrichtext' => [
                'identifier' => 'identifier3',
                'defaultValue' => null,
                'name' => 'Baz',
                'position' => 5,
            ],
        ];

        foreach ($fieldDefinitionsConfig as $fieldTypeIdentifier => $config) {
            $fieldDefinitions[] = $this->createFieldDefinition(
                $fieldTypeIdentifier,
                $config['identifier'],
                $config['name'],
                $config['position'],
                $config['defaultValue']
            );
        }

        return $fieldDefinitions;
    }

    /**
     * @param array<string, array{
     *     'fieldTypeIdentifier': string,
     *     'identifier': string,
     *     'defaultValue': ?\Ibexa\Contracts\Core\Repository\Values\ValueObject,
     *     'name': string,
     *     'position': int,
     * }> $fieldDefinitions
     */
    private function createContentTypeDraft(array $fieldDefinitions): ContentTypeDraft
    {
        return new ContentTypeDraft([
            'innerContentType' => new ContentType([
                'id' => 123,
                'fieldDefinitions' => $fieldDefinitions,
                'identifier' => 'identifier',
                'remoteId' => 'remoteId',
                'urlAliasSchema' => 'urlAliasSchema',
                'nameSchema' => 'nameSchema',
                'isContainer' => true,
                'mainLanguageCode' => 'fre-FR',
                'defaultSortField' => Location::SORT_FIELD_NAME,
                'defaultSortOrder' => Location::SORT_ORDER_ASC,
                'defaultAlwaysAvailable' => true,
                'names' => ['fre-FR' => 'Français', 'eng-GB' => 'English'],
                'descriptions' => ['fre-FR' => 'Vive le sucre !!!', 'eng-GB' => 'Sugar rules!!!'],
            ]),
        ]);
    }

    private function createFieldDefinition(
        string $fieldTypeIdentifier,
        string $identifier,
        string $name,
        int $position,
        ?ValueObject $defaultValue
    ): FieldDefinition {
        return new FieldDefinition([
            'identifier' => $identifier,
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
            'names' => ['fre-FR' => $name],
            'descriptions' => ['fre-FR' => 'some description'],
            'fieldGroup' => 'foo',
            'position' => $position,
            'isTranslatable' => true,
            'isRequired' => true,
            'isInfoCollector' => false,
            'validatorConfiguration' => ['validator' => 'config'],
            'fieldSettings' => ['field' => 'settings'],
            'defaultValue' => $defaultValue,
            'isSearchable' => true,
        ]);
    }

    private function createContentTypeData(ContentTypeDraft $contentTypeDraft): ContentTypeData
    {
        return new ContentTypeData([
            'contentTypeDraft' => $contentTypeDraft,
            'identifier' => $contentTypeDraft->identifier,
            'remoteId' => $contentTypeDraft->remoteId,
            'urlAliasSchema' => $contentTypeDraft->urlAliasSchema,
            'nameSchema' => $contentTypeDraft->nameSchema,
            'isContainer' => $contentTypeDraft->isContainer,
            'mainLanguageCode' => $contentTypeDraft->mainLanguageCode,
            'defaultSortField' => $contentTypeDraft->defaultSortField,
            'defaultSortOrder' => $contentTypeDraft->defaultSortOrder,
            'defaultAlwaysAvailable' => $contentTypeDraft->defaultAlwaysAvailable,
            'names' => $contentTypeDraft->getNames(),
            'descriptions' => $contentTypeDraft->getDescriptions(),
            'languageCode' => $contentTypeDraft->mainLanguageCode,
        ]);
    }

    /**
     * @param array<\Ibexa\Core\Repository\Values\ContentType\FieldDefinition> $fieldDefinitions
     */
    private function createFieldDefinitionsData(
        array $fieldDefinitions,
        ContentTypeData $contentTypeData
    ): array {
        $fieldDefinitionsData = [];
        foreach ($fieldDefinitions as $fieldDefinition) {
            $fieldDefinitionsData[] = $this->createFieldDefinitionData($fieldDefinition, $contentTypeData);
        }

        return $fieldDefinitionsData;
    }

    private function createFieldDefinitionData(
        FieldDefinition $fieldDefinition,
        ContentTypeData $contentTypeData
    ): FieldDefinitionData {
        return new FieldDefinitionData([
            'fieldDefinition' => $fieldDefinition,
            'contentTypeData' => $contentTypeData,
            'identifier' => $fieldDefinition->identifier,
            'names' => $fieldDefinition->names,
            'descriptions' => $fieldDefinition->descriptions,
            'fieldGroup' => $fieldDefinition->fieldGroup,
            'position' => $fieldDefinition->position,
            'isTranslatable' => $fieldDefinition->isTranslatable,
            'isRequired' => $fieldDefinition->isRequired,
            'isInfoCollector' => $fieldDefinition->isInfoCollector,
            'validatorConfiguration' => $fieldDefinition->validatorConfiguration,
            'fieldSettings' => $fieldDefinition->fieldSettings,
            'defaultValue' => $fieldDefinition->defaultValue,
            'isSearchable' => $fieldDefinition->isSearchable,
        ]);
    }

    private function mockContentTypeFieldTypesResolverGetFieldTypes(): void
    {
        $this->contentTypeFieldTypesResolver
            ->expects(self::once())
            ->method('getFieldTypes')
            ->willReturn(
                [
                    'identifier1' => [
                        'meta' => true,
                    ],
                ]
            );
    }

    private function mockEventDispatcherDispatch(): void
    {
        $this->eventDispatcher
            ->method('dispatch')
            ->with($this->isInstanceOf(FieldDefinitionMappingEvent::class), FieldDefinitionMappingEvent::NAME)
            ->willReturnCallback(
                static function (FieldDefinitionMappingEvent $event, string $eventName): Event {
                    $fieldDefinitionData = $event->getFieldDefinitionData();
                    $fieldDefinition = $event->getFieldDefinition();

                    $fieldDefinitionData->identifier = $fieldDefinition->identifier;
                    $fieldDefinitionData->names = $fieldDefinition->getNames();
                    $fieldDefinitionData->descriptions = $fieldDefinition->getDescriptions();
                    $fieldDefinitionData->fieldGroup = $fieldDefinition->fieldGroup;
                    $fieldDefinitionData->position = $fieldDefinition->position;
                    $fieldDefinitionData->isTranslatable = $fieldDefinition->isTranslatable;
                    $fieldDefinitionData->isRequired = $fieldDefinition->isRequired;
                    $fieldDefinitionData->isInfoCollector = $fieldDefinition->isInfoCollector;
                    $fieldDefinitionData->validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
                    $fieldDefinitionData->fieldSettings = $fieldDefinition->getFieldSettings();
                    $fieldDefinitionData->defaultValue = $fieldDefinition->defaultValue;
                    $fieldDefinitionData->isSearchable = $fieldDefinition->isSearchable;

                    $event->setFieldDefinitionData($fieldDefinitionData);

                    return $event;
                }
            );
    }

    private function mockFieldGroupListGetDefaultGroup(): void
    {
        $this->fieldGroupList
            ->method('getDefaultGroup')
            ->willReturn('foo');
    }
}

class_alias(ContentTypeDraftMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\Data\FormMapper\ContentTypeDraftMapperTest');
