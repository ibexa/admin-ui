<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\Bundle\AdminUi\Templating\Twig\SecurityExtension;
use Twig\Test\IntegrationTestCase;

final class SecurityExtensionTest extends IntegrationTestCase
{
    private const string CSRF_TOKEN_INTENTION = 'foo_intention';

    protected function getExtensions(): array
    {
        return [
            new SecurityExtension(self::CSRF_TOKEN_INTENTION),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/_fixtures/security/';
    }
}
