<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\URL;

use Ibexa\Contracts\Core\Repository\Values\URL\URLUpdateStruct;

class URLUpdateData extends URLUpdateStruct
{
    /** @var int */
    public $id;
}

class_alias(URLUpdateData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\URL\URLUpdateData');
