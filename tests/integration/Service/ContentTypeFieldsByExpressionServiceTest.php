<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\Service;

use Ibexa\Contracts\AdminUi\Service\ContentTypeFieldsByExpressionServiceInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;

final class ContentTypeFieldsByExpressionServiceTest extends IbexaKernelTestCase
{
    private ContentTypeFieldsByExpressionServiceInterface $fieldsFromExpressionService;

    private ContentTypeService $contentTypeService;

    protected function setUp(): void
    {
        self::bootKernel();
        self::setAdministratorUser();

        $this->fieldsFromExpressionService = self::getServiceByClassName(ContentTypeFieldsByExpressionServiceInterface::class);
        $this->contentTypeService = self::getServiceByClassName(ContentTypeService::class);
    }

    public function testExtractWithContentTypeGroupNames(): void
    {
        $expression = '{Content}/folder/name';

        $extractedFieldDefinitions = $this->fieldsFromExpressionService->getFieldsFromExpression($expression);

        self::assertCount(1, $extractedFieldDefinitions);
        $fieldDefinition = $extractedFieldDefinitions[0];
        self::assertSame('name', $fieldDefinition->identifier);
        self::assertSame('ezstring', $fieldDefinition->fieldTypeIdentifier);
    }

    public function testFieldIdWithinExpression(): void
    {
        $expression = '{Content}/folder/name';

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier('folder');
        $fieldDefinitions = $contentType->getFieldDefinitions();
        $nameFieldDefinition = $fieldDefinitions->filter(
            static fn (FieldDefinition $fieldDefinition): bool => $fieldDefinition->getIdentifier() === 'name'
        )->first();

        $result = $this->fieldsFromExpressionService->isFieldIncludedInExpression(
            $nameFieldDefinition,
            $expression,
        );

        self::assertTrue($result);
    }
}
