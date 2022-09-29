<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\Parser\Templates;

class LimitationValueTemplates extends Templates
{
    public const NODE_KEY = 'limitation_value_templates';
    public const INFO = 'Settings for limitation value templates';
    public const INFO_TEMPLATE_KEY = 'Template file where to find block definition to display limitation values';
}

class_alias(LimitationValueTemplates::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\Configuration\Parser\LimitationValueTemplates');
