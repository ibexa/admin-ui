<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Menu;

use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * Builds menu with "Copy" and "Cancel" items.
 */
final class CopyFormContextMenuBuilder extends AbstractFormContextMenuBuilder implements TranslationContainerInterface
{
    protected static function getSidebarType(): string
    {
        return 'copy';
    }

    protected static function getSidebarActionMessage(): string
    {
        return 'Copy';
    }
}
