<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification\ContentType;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\TestCase;

final class ContentTypeIsUserTest extends TestCase
{
    /**
     * @covers \Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser::isSatisfiedBy
     */
    public function testIsSatisfiedByInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'$contentType\' is invalid: Must be an instance of Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType');

        $specification = new ContentTypeIsUser([]);
        $specification->isSatisfiedBy(new \stdClass());
    }

    /**
     * @covers \Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser::isSatisfiedBy
     */
    public function testIsSatisfiedByCustomUserContentType(): void
    {
        $customUserContentType = 'custom_user_content_type';

        $specification = new ContentTypeIsUser([
            $customUserContentType,
        ]);

        self::assertTrue(
            $specification->isSatisfiedBy($this->createContentType($customUserContentType))
        );
    }

    /**
     * @covers \Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser::isSatisfiedBy
     */
    public function testIsSatisfiedByContentTypeWithIbexaUserField(): void
    {
        $specification = new ContentTypeIsUser([]);

        $contentTypeWithEzUserField = $this->createContentType(
            'ibexa_user',
            ['ibexa_string', 'ibexa_user']
        );

        self::assertTrue($specification->isSatisfiedBy($contentTypeWithEzUserField));
    }

    /**
     * @covers \Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser::isSatisfiedBy
     */
    public function testIsSatisfiedByReturnFalse(): void
    {
        $specification = new ContentTypeIsUser([
            'content_type_a', 'content_type_b', 'content_type_c',
        ]);

        $articleContentType = $this->createContentType('article', ['ibexa_string', 'ibexa_richtext']);

        self::assertFalse($specification->isSatisfiedBy($articleContentType));
    }

    /**
     * @param string[] $fieldsTypes
     */
    private function createContentType(string $identifier, array $fieldsTypes = []): APIContentType
    {
        $contentType = $this->createMock(ContentType::class);
        $contentType
            ->method('getIdentifier')
            ->willReturn($identifier);

        $contentType
            ->method('hasFieldDefinitionOfType')
            ->willReturnCallback(static function (string $fieldTypeIdentifier) use ($fieldsTypes): bool {
                return in_array($fieldTypeIdentifier, $fieldsTypes);
            });

        return $contentType;
    }
}
