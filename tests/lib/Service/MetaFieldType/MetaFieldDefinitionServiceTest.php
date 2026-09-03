<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Service\MetaFieldType;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\AdminUi\Service\MetaFieldType\MetaFieldDefinitionService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Ibexa\Tests\AdminUi\Service\MetaFieldType\Stub\ContentTypeDraftStub;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MetaFieldDefinitionServiceTest extends TestCase
{
    private const SINGULAR_FIELD_TYPE_IDENTIFIER = 'ibexa_seo';

    private const NON_SINGULAR_FIELD_TYPE_IDENTIFIER = 'eztext';

    private const DEFAULT_FIELD_GROUP = 'content';

    private const OTHER_FIELD_GROUP = 'other-group';

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService&\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeService $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\FieldTypeService&\PHPUnit\Framework\MockObject\MockObject */
    private FieldTypeService $fieldTypeService;

    /** @var \Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface&\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList&\PHPUnit\Framework\MockObject\MockObject */
    private FieldsGroupsList $fieldsGroupsList;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService&\PHPUnit\Framework\MockObject\MockObject */
    private LanguageService $languageService;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface&\PHPUnit\Framework\MockObject\MockObject */
    private ConfigResolverInterface $configResolver;

    private MetaFieldDefinitionService $metaFieldDefinitionService;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->fieldTypeService = $this->createMock(FieldTypeService::class);
        $this->contentTypeFieldTypesResolver = $this->createMock(ContentTypeFieldTypesResolverInterface::class);
        $this->fieldsGroupsList = $this->createMock(FieldsGroupsList::class);
        $this->languageService = $this->createMock(LanguageService::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);

        $this->configResolver
            ->method('hasParameter')
            ->willReturn(false);

        $this->fieldsGroupsList
            ->method('getDefaultGroup')
            ->willReturn(self::DEFAULT_FIELD_GROUP);

        $this->languageService
            ->method('getDefaultLanguageCode')
            ->willReturn('eng-GB');
        $this->languageService
            ->method('loadLanguage')
            ->willReturn(new Language(['languageCode' => 'eng-GB']));

        $this->contentTypeService
            ->method('newFieldDefinitionCreateStruct')
            ->willReturnCallback(
                static fn (string $identifier, string $fieldTypeIdentifier): FieldDefinitionCreateStruct => new FieldDefinitionCreateStruct([
                    'identifier' => $identifier,
                    'fieldTypeIdentifier' => $fieldTypeIdentifier,
                ])
            );

        $localeConverter = $this->createMock(LocaleConverterInterface::class);
        $localeConverter->method('convertToPOSIX')->willReturn('en_GB');

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Label');

        $this->metaFieldDefinitionService = new MetaFieldDefinitionService(
            $this->configResolver,
            $this->contentTypeFieldTypesResolver,
            $this->contentTypeService,
            $this->fieldTypeService,
            $this->fieldsGroupsList,
            $this->languageService,
            $localeConverter,
            $translator
        );
    }

    public function testMetaFieldDefinitionExistsForSingularFieldIgnoresFieldGroup(): void
    {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition(self::SINGULAR_FIELD_TYPE_IDENTIFIER, self::OTHER_FIELD_GROUP),
        ]);

        self::assertTrue(
            $this->metaFieldDefinitionService->metaFieldDefinitionExists(
                self::SINGULAR_FIELD_TYPE_IDENTIFIER,
                null,
                $contentType
            )
        );
    }

    public function testMetaFieldDefinitionExistsForNonSingularFieldRequiresMatchingFieldGroup(): void
    {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition(self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER, self::OTHER_FIELD_GROUP),
        ]);

        self::assertFalse(
            $this->metaFieldDefinitionService->metaFieldDefinitionExists(
                self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER,
                self::DEFAULT_FIELD_GROUP,
                $contentType
            )
        );

        self::assertTrue(
            $this->metaFieldDefinitionService->metaFieldDefinitionExists(
                self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER,
                self::OTHER_FIELD_GROUP,
                $contentType
            )
        );
    }

    public function testAddMetaFieldDefinitionsDoesNotDuplicateSingularFieldAlreadyPresentInDifferentGroup(): void
    {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition(self::SINGULAR_FIELD_TYPE_IDENTIFIER, self::OTHER_FIELD_GROUP),
        ]);

        $this->contentTypeFieldTypesResolver
            ->method('getMetaFieldTypes')
            ->willReturn([
                self::SINGULAR_FIELD_TYPE_IDENTIFIER => ['meta' => true, 'position' => 1],
            ]);

        $this->fieldTypeService
            ->method('getFieldType')
            ->with(self::SINGULAR_FIELD_TYPE_IDENTIFIER)
            ->willReturn($this->createFieldType(true));

        $this->contentTypeService
            ->expects(self::never())
            ->method('addFieldDefinition');

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType);
    }

    public function testAddMetaFieldDefinitionsAddsNonSingularFieldEvenIfPresentInDifferentGroup(): void
    {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition(self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER, self::OTHER_FIELD_GROUP),
        ]);

        $this->contentTypeFieldTypesResolver
            ->method('getMetaFieldTypes')
            ->willReturn([
                self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER => ['meta' => true, 'position' => 1],
            ]);

        $this->fieldTypeService
            ->method('getFieldType')
            ->with(self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER)
            ->willReturn($this->createFieldType(false));

        $this->contentTypeService
            ->expects(self::once())
            ->method('addFieldDefinition');

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType);
    }

    public function testAddMetaFieldDefinitionsAddsSingularFieldWhenNotYetPresent(): void
    {
        $contentType = $this->createContentTypeDraft([]);

        $this->contentTypeFieldTypesResolver
            ->method('getMetaFieldTypes')
            ->willReturn([
                self::SINGULAR_FIELD_TYPE_IDENTIFIER => ['meta' => true, 'position' => 1],
            ]);

        $this->fieldTypeService
            ->method('getFieldType')
            ->with(self::SINGULAR_FIELD_TYPE_IDENTIFIER)
            ->willReturn($this->createFieldType(true));

        $this->contentTypeService
            ->expects(self::once())
            ->method('addFieldDefinition');

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType);
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition> $fieldDefinitions
     */
    private function createContentTypeDraft(array $fieldDefinitions): ContentTypeDraftStub
    {
        return new ContentTypeDraftStub(new FieldDefinitionCollection($fieldDefinitions));
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
     * @return \Ibexa\Contracts\Core\Repository\FieldType&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createFieldType(bool $isSingular): FieldType
    {
        $fieldType = $this->createMock(FieldType::class);
        $fieldType->method('isSingular')->willReturn($isSingular);

        return $fieldType;
    }
}
