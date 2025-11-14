<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentType;

use Ibexa\Rest\Value as RestValue;

final class FieldDefinitionExpression extends RestValue
{
    public string $expression;

    public ?string $configuration = null;

    public function __construct(string $expression, ?string $configuration = null)
    {
        $this->expression = $expression;
        $this->configuration = $configuration;
    }
}
