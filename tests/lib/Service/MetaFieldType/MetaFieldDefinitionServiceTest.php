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
use Ibexa\Core\Base\Exceptions\NotFoundException;
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

    private const MISSING_FIELD_TYPE_IDENTIFIER = 'ibexa_missing';

    private const DEFAULT_FIELD_GROUP = 'content';

    private const OTHER_FIELD_GROUP = 'other-group';

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService&\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeService $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\FieldTypeService&\PHPUnit\Framework\MockObject\MockObject */
    private FieldTypeService $fieldTypeService;

    /** @var \Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface&\PHPUnit\Framework\MockObject\Stub */
    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList&\PHPUnit\Framework\MockObject\Stub */
    private FieldsGroupsList $fieldsGroupsList;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService&\PHPUnit\Framework\MockObject\Stub */
    private LanguageService $languageService;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface&\PHPUnit\Framework\MockObject\Stub */
    private ConfigResolverInterface $configResolver;

    private MetaFieldDefinitionService $metaFieldDefinitionService;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->fieldTypeService = $this->createMock(FieldTypeService::class);
        $this->contentTypeFieldTypesResolver = $this->createStub(ContentTypeFieldTypesResolverInterface::class);
        $this->fieldsGroupsList = $this->createStub(FieldsGroupsList::class);
        $this->languageService = $this->createStub(LanguageService::class);
        $this->configResolver = $this->createStub(ConfigResolverInterface::class);

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

        $localeConverter = $this->createStub(LocaleConverterInterface::class);
        $localeConverter->method('convertToPOSIX')->willReturn('en_GB');

        $translator = $this->createStub(TranslatorInterface::class);
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

    /**
     * @dataProvider provideFieldGroupsForExistenceCheck
     */
    public function testMetaFieldDefinitionExists(
        string $fieldTypeIdentifier,
        string $existingFieldGroup,
        ?string $queryFieldGroup,
        bool $expectedResult
    ): void {
        $contentType = $this->createContentTypeDraft([
            $this->createFieldDefinition($fieldTypeIdentifier, $existingFieldGroup),
        ]);

        self::assertSame(
            $expectedResult,
            $this->metaFieldDefinitionService->metaFieldDefinitionExists(
                $fieldTypeIdentifier,
                $queryFieldGroup,
                $contentType
            )
        );
    }

    /**
     * @return iterable<string, array{string, string, ?string, bool}>
     */
    public static function provideFieldGroupsForExistenceCheck(): iterable
    {
        yield 'singular field ignores field group' => [
            self::SINGULAR_FIELD_TYPE_IDENTIFIER,
            self::OTHER_FIELD_GROUP,
            null,
            true,
        ];

        yield 'non-singular field in different group is not found' => [
            self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER,
            self::OTHER_FIELD_GROUP,
            self::DEFAULT_FIELD_GROUP,
            false,
        ];

        yield 'non-singular field in matching group is found' => [
            self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER,
            self::OTHER_FIELD_GROUP,
            self::OTHER_FIELD_GROUP,
            true,
        ];
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
            ->expects(self::once())
            ->method('getFieldType')
            ->with(self::SINGULAR_FIELD_TYPE_IDENTIFIER)
            ->willReturn($this->createFieldType(true));

        $this->contentTypeService
            ->expects(self::never())
            ->method('addFieldDefinition');

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType);
    }

    public function testAddMetaFieldDefinitionsSkipsMetaFieldTypeWhenFieldTypeIsNotFound(): void
    {
        $contentType = $this->createContentTypeDraft([]);

        $this->contentTypeFieldTypesResolver
            ->method('getMetaFieldTypes')
            ->willReturn([
                self::MISSING_FIELD_TYPE_IDENTIFIER => ['meta' => true, 'position' => 1],
                self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER => ['meta' => true, 'position' => 2],
            ]);

        $this->fieldTypeService
            ->expects(self::exactly(2))
            ->method('getFieldType')
            ->with(self::logicalOr(self::MISSING_FIELD_TYPE_IDENTIFIER, self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER))
            ->willReturnCallback(
                function (string $fieldTypeIdentifier): FieldType {
                    if ($fieldTypeIdentifier === self::MISSING_FIELD_TYPE_IDENTIFIER) {
                        throw new NotFoundException('FieldType', $fieldTypeIdentifier);
                    }

                    return $this->createFieldType(false);
                }
            );

        $this->contentTypeService
            ->expects(self::once())
            ->method('addFieldDefinition')
            ->with(
                $contentType,
                self::callback(
                    static fn (FieldDefinitionCreateStruct $struct): bool => $struct->fieldTypeIdentifier === self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER
                )
            );

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType);
    }

    /**
     * @dataProvider provideNonDuplicateAdditionScenarios
     *
     * @param array<array{string, string}> $existingFieldDefinitions
     */
    public function testAddMetaFieldDefinitionsAddsFieldWhenNotAlreadyPresentInMatchingGroup(
        array $existingFieldDefinitions,
        string $fieldTypeIdentifier,
        bool $isSingular
    ): void {
        $contentType = $this->createContentTypeDraft(array_map(
            fn (array $fieldDefinition): FieldDefinition => $this->createFieldDefinition(...$fieldDefinition),
            $existingFieldDefinitions
        ));

        $this->contentTypeFieldTypesResolver
            ->method('getMetaFieldTypes')
            ->willReturn([
                $fieldTypeIdentifier => ['meta' => true, 'position' => 1],
            ]);

        $this->fieldTypeService
            ->expects(self::once())
            ->method('getFieldType')
            ->with($fieldTypeIdentifier)
            ->willReturn($this->createFieldType($isSingular));

        $this->contentTypeService
            ->expects(self::once())
            ->method('addFieldDefinition')
            ->with(
                $contentType,
                self::callback(
                    static fn (FieldDefinitionCreateStruct $struct): bool => $struct->fieldTypeIdentifier === $fieldTypeIdentifier
                )
            );

        $this->metaFieldDefinitionService->addMetaFieldDefinitions($contentType);
    }

    /**
     * @return iterable<string, array{array<array{string, string}>, string, bool}>
     */
    public static function provideNonDuplicateAdditionScenarios(): iterable
    {
        yield 'non-singular field present only in different group is still added' => [
            [[self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER, self::OTHER_FIELD_GROUP]],
            self::NON_SINGULAR_FIELD_TYPE_IDENTIFIER,
            false,
        ];

        yield 'singular field not yet present is added' => [
            [],
            self::SINGULAR_FIELD_TYPE_IDENTIFIER,
            true,
        ];
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
     * @return \Ibexa\Contracts\Core\Repository\FieldType&\PHPUnit\Framework\MockObject\Stub
     */
    private function createFieldType(bool $isSingular): FieldType
    {
        $fieldType = $this->createStub(FieldType::class);
        $fieldType
            ->method('isSingular')
            ->willReturn($isSingular);

        return $fieldType;
    }
}
