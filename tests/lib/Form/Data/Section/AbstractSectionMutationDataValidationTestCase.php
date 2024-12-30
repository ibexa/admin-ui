<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data\Section;

use Ibexa\Tests\AdminUi\Form\Data\AbstractFormDataValidationTestCase;
use Ibexa\Tests\AdminUi\Form\Data\FormErrorDataTestWrapper;

/**
 * @internal
 */
abstract class AbstractSectionMutationDataValidationTestCase extends AbstractFormDataValidationTestCase
{
    public static function getDataForTestFormSubmitValidation(): iterable
    {
        yield 'invalid pattern' => [
            [
                'identifier' => 'Foo With Space',
                'name' => 'Foo',
            ],
            [
                new FormErrorDataTestWrapper(
                    'ez.section.identifier.format',
                    [
                        '{{ value }}' => '"Foo With Space"',
                        '{{ pattern }}' => '/^[[:alnum:]_]+$/',
                    ],
                    'data.identifier'
                ),
            ],
        ];

        yield 'valid pattern' => [
            [
                'identifier' => 'Foo_Identifier009',
                'name' => 'Foo',
            ],
            [
            ],
        ];
    }
}
