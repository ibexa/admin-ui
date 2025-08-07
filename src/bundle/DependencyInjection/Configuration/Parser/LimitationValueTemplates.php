<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\Parser\Templates;

final class LimitationValueTemplates extends Templates
{
    public const string NODE_KEY = 'limitation_value_templates';
    public const string INFO = 'Settings for limitation value templates';
    public const string INFO_TEMPLATE_KEY = 'Template file where to find block definition to display limitation values';
}
