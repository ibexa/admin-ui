<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Fields;

use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

interface FieldTypeComponentInterface
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function setValue(array $parameters): void;

    /**
     * @return array<string|int, mixed>
     */
    public function getValue(): array;

    /**
     * @param array<string, mixed> $values
     */
    public function verifyValueInItemView(array $values): void;

    /**
     * @param array<string, mixed> $values
     */
    public function verifyValueInEditView(array $values): void;

    public function getFieldTypeIdentifier(): string;

    public function setParentLocator(VisibleCSSLocator $selector): void;
}
