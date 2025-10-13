<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data;

/**
 * @internal
 *
 * Wrapper for form errors, simplifying tests DX
 */
final readonly class FormErrorDataTestWrapper
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public string $message,
        public array $parameters,
        public string $propertyPath
    ) {
    }
}
