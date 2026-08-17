<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Service\MetaFieldType;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\AdminUi\Service\MetaFieldType\MetaFieldDefinitionService;
use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\AdminUiForms;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \Ibexa\AdminUi\Service\MetaFieldType\MetaFieldDefinitionService
 */
final class MetaFieldDefinitionServiceTest extends TestCase
{
    private const DEFAULT_GROUP = 'content';
    private const LANGUAGE_CODE = 'eng-GB';

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ConfigResolverInterface $configResolver;

    /** @var \Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeService $contentTypeService;

    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList|\PHPUnit\Framework\MockObject\MockObject */
    private FieldsGroupsList $fieldsGroupsList;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService|\PHPUnit\Framework\MockObject\MockObject */
    private LanguageService $languageService;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\LocaleConverterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private LocaleConverterInterface $localeConverter;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private TranslatorInterface $translator;

    private MetaFieldDefinitionService $metaFieldDefinitionService;

    protected function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->contentTypeFieldTypesResolver = $this->createMock(ContentTypeFieldTypesResolverInterface::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->fieldsGroupsList = $this->createMock(FieldsGroupsList::class);
        $this->languageService = $this->createMock(LanguageService::class);
        $this->localeConverter = $this->createMock(LocaleConverterInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->fieldsGroupsList->method('getDefaultGroup')->willReturn(self::DEFAULT_GROUP);

        $this->metaFieldDefinitionService = new MetaFieldDefinitionService(
            $this->configResolver,
            $this->contentTypeFieldTypesResolver,
            $this->contentTypeService,
            $this->fieldsGroupsList,
            $this->languageService,
            $this->localeConverter,
            $this->translator
        );
    }

    public function testAddMetaFieldDefinitionsAddsMissingMetaFieldToContentTypeCreateStruct(): void
    {
        $this->mockNoConfiguredDefaultMetaFieldTypeGroup();
        $this->contentTypeFieldTypesResolver->method('getMetaFieldTypes')->willReturn([
            'seo_metadata' => ['meta' => true, 'position' => 1, 'unique' => false],
        ]);
        $this->translator->method('trans')->willReturn('SEO Metadata');
        $this->localeConverter->method('convertToPOSIX')->willReturn('eng_GB');

        $contentType = new ContentTypeCreateStruct();
        $contentType->fieldDefinitions = [];

        $this->mockNewFieldDefinitionCreateStruct();

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType, $this->createLanguage());

        self::assertCount(1, $contentType->fieldDefinitions);
        self::assertSame('seo_metadata', $contentType->fieldDefinitions[0]->fieldTypeIdentifier);
        self::assertSame(self::DEFAULT_GROUP, $contentType->fieldDefinitions[0]->fieldGroup);
    }

    public function testAddMetaFieldDefinitionsSkipsExistingNonUniqueFieldInSameGroup(): void
    {
        $this->mockNoConfiguredDefaultMetaFieldTypeGroup();
        $this->contentTypeFieldTypesResolver->method('getMetaFieldTypes')->willReturn([
            'seo_metadata' => ['meta' => true, 'position' => 1, 'unique' => false],
        ]);

        $contentType = new ContentTypeCreateStruct();
        $existing = new FieldDefinitionCreateStruct();
        $existing->fieldTypeIdentifier = 'seo_metadata';
        $existing->fieldGroup = self::DEFAULT_GROUP;
        $contentType->fieldDefinitions = [$existing];

        $this->contentTypeService->expects(self::never())->method('newFieldDefinitionCreateStruct');

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType, $this->createLanguage());

        self::assertCount(1, $contentType->fieldDefinitions);
    }

    public function testAddMetaFieldDefinitionsSkipsExistingUniqueFieldRegardlessOfGroup(): void
    {
        $this->mockNoConfiguredDefaultMetaFieldTypeGroup();
        $this->contentTypeFieldTypesResolver->method('getMetaFieldTypes')->willReturn([
            'seo_metadata' => ['meta' => true, 'position' => 1, 'unique' => true],
        ]);

        $contentType = new ContentTypeCreateStruct();
        $existing = new FieldDefinitionCreateStruct();
        $existing->fieldTypeIdentifier = 'seo_metadata';
        $existing->fieldGroup = 'a_completely_different_group';
        $contentType->fieldDefinitions = [$existing];

        $this->contentTypeService->expects(self::never())->method('newFieldDefinitionCreateStruct');

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType, $this->createLanguage());

        self::assertCount(1, $contentType->fieldDefinitions);
    }

    public function testAddMetaFieldDefinitionsAddsDuplicateNonUniqueFieldWhenInDifferentGroup(): void
    {
        $this->mockNoConfiguredDefaultMetaFieldTypeGroup();
        $this->contentTypeFieldTypesResolver->method('getMetaFieldTypes')->willReturn([
            'seo_metadata' => ['meta' => true, 'position' => 1, 'unique' => false],
        ]);
        $this->translator->method('trans')->willReturn('SEO Metadata');
        $this->localeConverter->method('convertToPOSIX')->willReturn('eng_GB');
        $this->mockNewFieldDefinitionCreateStruct();

        $contentType = new ContentTypeCreateStruct();
        $existing = new FieldDefinitionCreateStruct();
        $existing->fieldTypeIdentifier = 'seo_metadata';
        $existing->fieldGroup = 'a_completely_different_group';
        $contentType->fieldDefinitions = [$existing];

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType, $this->createLanguage());

        self::assertCount(2, $contentType->fieldDefinitions);
    }

    public function testAddMetaFieldDefinitionsUsesContentTypeServiceForContentTypeDraft(): void
    {
        $this->mockNoConfiguredDefaultMetaFieldTypeGroup();
        $this->contentTypeFieldTypesResolver->method('getMetaFieldTypes')->willReturn([
            'seo_metadata' => ['meta' => true, 'position' => 1, 'unique' => false],
        ]);
        $this->translator->method('trans')->willReturn('SEO Metadata');
        $this->localeConverter->method('convertToPOSIX')->willReturn('eng_GB');
        $this->mockNewFieldDefinitionCreateStruct();

        $contentType = $this->createContentTypeDraft([]);

        $this->contentTypeService->expects(self::once())->method('addFieldDefinition')
            ->with($contentType, self::isInstanceOf(FieldDefinitionCreateStruct::class));

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType, $this->createLanguage());
    }

    public function testAddMetaFieldDefinitionsUsesDefaultLanguageWhenNoneGiven(): void
    {
        $this->mockNoConfiguredDefaultMetaFieldTypeGroup();
        $this->contentTypeFieldTypesResolver->method('getMetaFieldTypes')->willReturn([]);

        $this->languageService->expects(self::once())
            ->method('getDefaultLanguageCode')
            ->willReturn(self::LANGUAGE_CODE);
        $this->languageService->expects(self::once())
            ->method('loadLanguage')
            ->with(self::LANGUAGE_CODE)
            ->willReturn($this->createLanguage());

        $contentType = new ContentTypeCreateStruct();
        $contentType->fieldDefinitions = [];

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType);
    }

    public function testMetaFieldDefinitionExistsMatchesByIdentifierWhenGroupIsNull(): void
    {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition('seo_metadata', 'other_group'),
        ]);

        self::assertTrue(
            $this->metaFieldDefinitionService->metaFieldDefinitionExists('seo_metadata', null, $contentType)
        );
    }

    public function testMetaFieldDefinitionExistsRequiresMatchingGroupWhenGiven(): void
    {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition('seo_metadata', 'other_group'),
        ]);

        self::assertFalse(
            $this->metaFieldDefinitionService->metaFieldDefinitionExists('seo_metadata', self::DEFAULT_GROUP, $contentType)
        );
        self::assertTrue(
            $this->metaFieldDefinitionService->metaFieldDefinitionExists('seo_metadata', 'other_group', $contentType)
        );
    }

    public function testMetaFieldDefinitionExistsReturnsFalseWhenIdentifierIsMissing(): void
    {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition('seo_metadata', self::DEFAULT_GROUP),
        ]);

        self::assertFalse(
            $this->metaFieldDefinitionService->metaFieldDefinitionExists('other_field_type', null, $contentType)
        );
    }

    public function testCreateMetaFieldDefinitionCreateStruct(): void
    {
        $fieldDefinitionCreateStruct = new FieldDefinitionCreateStruct();
        $this->contentTypeService->expects(self::once())
            ->method('newFieldDefinitionCreateStruct')
            ->with(self::isType('string'), 'seo_metadata')
            ->willReturn($fieldDefinitionCreateStruct);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('seo_metadata.name', [], 'ibexa_fieldtypes', 'eng_GB')
            ->willReturn('SEO Metadata');

        $this->localeConverter->expects(self::once())
            ->method('convertToPOSIX')
            ->with(self::LANGUAGE_CODE)
            ->willReturn('eng_GB');

        $result = $this->metaFieldDefinitionService->createMetaFieldDefinitionCreateStruct(
            'seo_metadata',
            self::DEFAULT_GROUP,
            $this->createLanguage(),
            3
        );

        self::assertSame(self::DEFAULT_GROUP, $result->fieldGroup);
        self::assertSame(3, $result->position);
        self::assertSame([self::LANGUAGE_CODE => 'SEO Metadata'], $result->names);
    }

    public function testGetDefaultMetaDataFieldTypeGroupReturnsNullWhenParameterNotSet(): void
    {
        $this->configResolver->expects(self::once())
            ->method('hasParameter')
            ->with(AdminUiForms::CONTENT_TYPE_DEFAULT_META_FIELD_TYPE_GROUP_PARAM)
            ->willReturn(false);
        $this->configResolver->expects(self::never())->method('getParameter');

        self::assertNull($this->metaFieldDefinitionService->getDefaultMetaDataFieldTypeGroup());
    }

    public function testGetDefaultMetaDataFieldTypeGroupReturnsConfiguredValue(): void
    {
        $this->configResolver->expects(self::once())
            ->method('hasParameter')
            ->with(AdminUiForms::CONTENT_TYPE_DEFAULT_META_FIELD_TYPE_GROUP_PARAM)
            ->willReturn(true);
        $this->configResolver->expects(self::once())
            ->method('getParameter')
            ->with(AdminUiForms::CONTENT_TYPE_DEFAULT_META_FIELD_TYPE_GROUP_PARAM)
            ->willReturn('custom_group');

        self::assertSame(
            'custom_group',
            $this->metaFieldDefinitionService->getDefaultMetaDataFieldTypeGroup()
        );
    }

    private function mockNoConfiguredDefaultMetaFieldTypeGroup(): void
    {
        $this->configResolver->method('hasParameter')
            ->with(AdminUiForms::CONTENT_TYPE_DEFAULT_META_FIELD_TYPE_GROUP_PARAM)
            ->willReturn(false);
    }

    private function createLanguage(): Language
    {
        return new Language(['languageCode' => self::LANGUAGE_CODE]);
    }

    private function createFieldDefinition(string $fieldTypeIdentifier, string $fieldGroup): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => $fieldTypeIdentifier,
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
            'fieldGroup' => $fieldGroup,
        ]);
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition> $fieldDefinitions
     */
    private function createContentTypeDraft(array $fieldDefinitions): ContentTypeDraft
    {
        $fieldDefinitionCollection = new FieldDefinitionCollection($fieldDefinitions);

        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft|\PHPUnit\Framework\MockObject\MockObject $contentTypeDraft */
        $contentTypeDraft = $this->getMockForAbstractClass(
            ContentTypeDraft::class,
            [],
            '',
            true,
            true,
            true,
            ['getFieldDefinitions', '__get']
        );
        $contentTypeDraft->method('getFieldDefinitions')->willReturn($fieldDefinitionCollection);
        $contentTypeDraft->method('__get')->willReturnCallback(
            static fn (string $property): ?FieldDefinitionCollection => 'fieldDefinitions' === $property ? $fieldDefinitionCollection : null
        );

        return $contentTypeDraft;
    }

    private function mockNewFieldDefinitionCreateStruct(): void
    {
        $this->contentTypeService->method('newFieldDefinitionCreateStruct')
            ->willReturnCallback(static function (string $identifier, string $fieldTypeIdentifier): FieldDefinitionCreateStruct {
                $fieldDefinitionCreateStruct = new FieldDefinitionCreateStruct();
                $fieldDefinitionCreateStruct->identifier = $identifier;
                $fieldDefinitionCreateStruct->fieldTypeIdentifier = $fieldTypeIdentifier;

                return $fieldDefinitionCreateStruct;
            });
    }
}
