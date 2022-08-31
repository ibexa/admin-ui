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
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCollection as FieldDefinitionCollectionInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\FieldType\Value;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Ibexa\AdminUi\Form\Data\FormMapper\ContentTypeDraftMapper
 */
final class ContentTypeDraftMapperTest extends TestCase
{
    private const TAB_FIELD_DEF_IDENTIFIER = 'ezstring';

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
        $this->fieldsGroupsList = $this->createMock(FieldsGroupsList::class);

        $this->contentTypeDraftMapper = new ContentTypeDraftMapper(
            $this->contentTypeFieldTypesResolver,
            $this->contentTypeService,
            $this->eventDispatcher,
            $this->fieldsGroupsList
        );
    }

    public function testMapToFormData(): void
    {
        $fieldDefs = $this->createFieldDefinitionCollectionForFieldDefinitionsData();
        $tabsFieldDefs = $this->createFieldDefinitionCollectionForTabsFieldDefinitionsData();
        $contentType = $this->createContentType(
            new FieldDefinitionCollection(
                array_merge(
                    iterator_to_array($fieldDefs),
                    iterator_to_array($tabsFieldDefs),
                )
            )
        );
        $contentTypeDraft = $this->createContentTypeDraft($contentType);
        $expectedContentTypeData = $this->createContentTypeData($contentTypeDraft);
        $fieldDefinitionsData = $this->createFieldDefinitionsData($fieldDefs, $expectedContentTypeData);
        $tabsFieldDefinitionsData = $this->createFieldDefinitionsData($tabsFieldDefs, $expectedContentTypeData);

        foreach ($fieldDefinitionsData as $fieldDefinitionData) {
            $expectedContentTypeData->addFieldDefinitionData($fieldDefinitionData);
        }

        foreach ($tabsFieldDefinitionsData as $fieldDefinitionData) {
            $expectedContentTypeData->addTabsFieldDefinitionData($fieldDefinitionData);
        }

        $this->mockContentTypeFieldTypesResolverGetFieldTypes();
        $this->mockContentTypeServiceLoadContentType(123, $contentType);
        $this->mockEventDispatcherDispatch();
        $this->mockFieldGroupListGetDefaultGroup();

        self::assertEquals(
            $expectedContentTypeData,
            $this->contentTypeDraftMapper->mapToFormData($contentTypeDraft)
        );
    }

    private function createContentType(FieldDefinitionCollectionInterface $fieldDefinitionCollection): ContentType
    {
        return new ContentType([
            'id' => 123,
            'fieldDefinitions' => $fieldDefinitionCollection,
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
        ]);
    }

    private function createContentTypeDraft(ContentType $contentType): ContentTypeDraft
    {
        return new ContentTypeDraft(['innerContentType' => $contentType]);
    }

    private function createFieldDefinitionCollectionForFieldDefinitionsData(): FieldDefinitionCollectionInterface
    {
        return $this->createFieldDefinitionCollection(
            [
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
            ]
        );
    }

    private function createFieldDefinitionCollectionForTabsFieldDefinitionsData(): FieldDefinitionCollectionInterface
    {
        return $this->createFieldDefinitionCollection(
            [
                self::TAB_FIELD_DEF_IDENTIFIER => [
                    'identifier' => 'identifier1',
                    'defaultValue' => $this->getMockForAbstractClass(Value::class),
                    'name' => 'Foo',
                    'position' => 0,
                ],
            ]
        );
    }

    /**
     * @param array<string, array{
     *     'identifier': string,
     *     'defaultValue': ?\Ibexa\Contracts\Core\Repository\Values\ValueObject,
     *     'name': string,
     *     'position': int,
     * }> $fieldDefinitionsConfig
     */
    private function createFieldDefinitionCollection(array $fieldDefinitionsConfig): FieldDefinitionCollectionInterface
    {
        $fieldDefinitions = [];
        foreach ($fieldDefinitionsConfig as $fieldTypeIdentifier => $config) {
            $fieldDefinitions[] = $this->createFieldDefinition(
                $fieldTypeIdentifier,
                $config['identifier'],
                $config['name'],
                $config['position'],
                $config['defaultValue']
            );
        }

        return new FieldDefinitionCollection($fieldDefinitions);
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

    private function createFieldDefinitionsData(
        FieldDefinitionCollectionInterface $fieldDefinitionCollection,
        ContentTypeData $contentTypeData
    ): array {
        $fieldDefinitionsData = [];
        foreach ($fieldDefinitionCollection as $fieldDefinition) {
            $fieldDefinitionsData[] = $this->createFieldDefinitionData(
                $fieldDefinition,
                $contentTypeData,
                $fieldDefinition->fieldTypeIdentifier === self::TAB_FIELD_DEF_IDENTIFIER
            );
        }

        return $fieldDefinitionsData;
    }

    private function createFieldDefinitionData(
        FieldDefinition $fieldDefinition,
        ContentTypeData $contentTypeData,
        bool $enabled
    ): FieldDefinitionData {
        return new FieldDefinitionData([
            'enabled' => $enabled,
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

    private function mockContentTypeFieldTypesResolverGetFieldTypes(): void
    {
        $this->contentTypeFieldTypesResolver
            ->expects(self::once())
            ->method('getFieldTypes')
            ->willReturn(
                [
                    self::TAB_FIELD_DEF_IDENTIFIER => [
                        'meta' => true,
                    ],
                ]
            );
    }

    private function mockContentTypeServiceLoadContentType(int $contentTypeId, ContentType $contentType): void
    {
        $this->contentTypeService
            ->expects(self::once())
            ->method('loadContentType')
            ->with($contentTypeId)
            ->willReturn($contentType);
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
        $this->fieldsGroupsList
            ->method('getDefaultGroup')
            ->willReturn('foo');
    }
}

class_alias(ContentTypeDraftMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\Data\FormMapper\ContentTypeDraftMapperTest');
