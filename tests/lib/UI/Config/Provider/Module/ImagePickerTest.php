<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UI\Config\Provider\Module;

use Ibexa\AdminUi\UI\Config\Provider\Module\ImagePicker;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use PHPUnit\Framework\TestCase;

final class ImagePickerTest extends TestCase
{
    private const FIELD_DEFINITION_IDENTIFIERS = ['field_foo', 'field_bar'];
    private const CONTENT_TYPE_IDENTIFIERS = ['content_type_foo', 'content_type_bar'];
    private const AGGREGATIONS = [
        'KeywordTermAggregation' => [
            'name' => 'keywords',
            'contentTypeIdentifier' => 'keywords',
            'fieldDefinitionIdentifier' => 'keywords',
        ],
    ];

    private ProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new ImagePicker(
            [
                'imageFieldDefinitionIdentifiers' => self::FIELD_DEFINITION_IDENTIFIERS,
                'imageContentTypeIdentifiers' => self::CONTENT_TYPE_IDENTIFIERS,
                'aggregations' => self::AGGREGATIONS,
            ]
        );
    }

    public function testGetConfig(): void
    {
        self::assertSame(
            [
                'imageFieldDefinitionIdentifiers' => self::FIELD_DEFINITION_IDENTIFIERS,
                'imageContentTypeIdentifiers' => self::CONTENT_TYPE_IDENTIFIERS,
                'aggregations' => self::AGGREGATIONS,
            ],
            $this->provider->getConfig()
        );
    }
}
