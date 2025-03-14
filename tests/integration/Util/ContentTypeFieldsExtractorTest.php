<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\Util;

use Ibexa\AdminUi\Util\ContentTypeFieldsExtractorInterface;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;
use LogicException;

final class ContentTypeFieldsExtractorTest extends IbexaKernelTestCase
{
    private ContentTypeFieldsExtractorInterface $contentTypeFieldsExtractor;

    private ContentTypeHandler $contentTypeHandler;

    protected function setUp(): void
    {
        self::bootKernel();
        self::setAdministratorUser();

        $this->contentTypeFieldsExtractor = self::getServiceByClassName(ContentTypeFieldsExtractorInterface::class);
        $this->contentTypeHandler = self::getServiceByClassName(ContentTypeHandler::class);
    }

    public function testExtractWithContentTypeGroupNames(): void
    {
        $expression = '{Media,Content}/*/name';

        $extractedFieldIds = $this->contentTypeFieldsExtractor->extractFieldsFromExpression($expression);

        foreach ($extractedFieldIds as $fieldId) {
            $fieldDefinition = $this->contentTypeHandler->getFieldDefinition($fieldId, 0);

            self::assertSame('name', $fieldDefinition->identifier);
        }
    }

    public function testExtractWithContentTypeNames(): void
    {
        $expression = '*/user/{first_name,last_name}';

        $extractedFieldIds = $this->contentTypeFieldsExtractor->extractFieldsFromExpression($expression);

        $firstNameField = $this->contentTypeHandler->getFieldDefinition($extractedFieldIds[0], 0);
        $lastNameField = $this->contentTypeHandler->getFieldDefinition($extractedFieldIds[1], 0);

        self::assertSame('first_name', $firstNameField->identifier);
        self::assertSame('last_name', $lastNameField->identifier);
    }

    public function testExtractWithContentTypeAndGroupNames(): void
    {
        $expression = 'Users/user/{first_name,last_name}';

        $extractedFieldIds = $this->contentTypeFieldsExtractor->extractFieldsFromExpression($expression);

        $firstNameField = $this->contentTypeHandler->getFieldDefinition($extractedFieldIds[0], 0);
        $lastNameField = $this->contentTypeHandler->getFieldDefinition($extractedFieldIds[1], 0);

        self::assertSame('first_name', $firstNameField->identifier);
        self::assertSame('last_name', $lastNameField->identifier);
    }

    public function testExtractWithContentTypeAndGroupNamesFailsWithTypesOutsideGroups(): void
    {
        self::expectException(LogicException::class);

        $expression = 'Content/user/{first_name,last_name}';

        $this->contentTypeFieldsExtractor->extractFieldsFromExpression($expression);
    }
}
