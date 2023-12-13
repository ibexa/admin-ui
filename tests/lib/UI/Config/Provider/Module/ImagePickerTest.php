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
    private const FIELD_DEFINITION_IDENTIFIERS = ['foo', 'bar'];

    private ProviderInterface $provider;

    protected function setUp(): void
    {
        $this->provider = new ImagePicker(self::FIELD_DEFINITION_IDENTIFIERS);
    }

    public function testGetConfig(): void
    {
        self::assertSame(
            [
                'imageFieldDefinitionIdentifiers' => self::FIELD_DEFINITION_IDENTIFIERS,
            ],
            $this->provider->getConfig()
        );
    }
}
