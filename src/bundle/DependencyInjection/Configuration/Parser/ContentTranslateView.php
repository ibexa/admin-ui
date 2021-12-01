<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\Parser\View;

class ContentTranslateView extends View
{
    const NODE_KEY = 'content_translate_view';
    const INFO = 'Template selection settings when displaying a content translate form';
}

class_alias(ContentTranslateView::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\Configuration\Parser\ContentTranslateView');
