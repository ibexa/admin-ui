<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

interface ContentRightSidebarLabelFactoryInterface
{
    public function createLabel(ContentType $contentType): string;
}

class_alias(ContentRightSidebarLabelFactoryInterface::class, 'EzSystems\EzPlatformAdminUi\Menu\ContentRightSidebarLabelFactoryInterface');
