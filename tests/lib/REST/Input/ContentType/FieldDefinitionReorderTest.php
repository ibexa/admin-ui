<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\REST\Input\ContentType;

use Ibexa\AdminUi\REST\Input\Parser\ContentType\FieldDefinitionReorder;
use Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionReorder as FieldDefinitionReorderValue;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use PHPUnit\Framework\TestCase;

final class FieldDefinitionReorderTest extends TestCase
{
    /** @var \Ibexa\AdminUi\REST\Input\Parser\ContentType\FieldDefinitionReorder */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new FieldDefinitionReorder();
    }

    public function testValidInput(): void
    {
        self::assertEquals(
            new FieldDefinitionReorderValue(['foo', 'bar', 'baz']),
            $this->parser->parse(
                [
                    'fieldDefinitionIdentifiers' => ['foo', 'bar', 'baz'],
                ],
                $this->createMock(ParsingDispatcher::class)
            )
        );
    }

    public function testInvalidInput(): void
    {
        $this->expectException(Exceptions\Parser::class);
        $this->expectExceptionMessage("Missing or invalid 'fieldDefinitionIdentifiers' property for Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionReorder.");

        $this->parser->parse(
            [],
            $this->createMock(ParsingDispatcher::class)
        );
    }
}
