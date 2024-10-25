<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Menu;

use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * Builds menu with "Update" and "Cancel" items.
 */
final class UpdateFormContextMenuBuilder extends AbstractFormContextMenuBuilder implements TranslationContainerInterface
{
    protected static function getSidebarType(): string
    {
        return 'update';
    }

    protected static function getSidebarActionMessage(): string
    {
        return 'Save and close';
    }
}
