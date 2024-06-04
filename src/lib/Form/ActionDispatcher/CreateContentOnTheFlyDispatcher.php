<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\ActionDispatcher;

use Ibexa\ContentForms\Form\ActionDispatcher\ContentDispatcher;
use Ibexa\Contracts\AdminUi\Event\ContentOnTheFlyEvents;

class CreateContentOnTheFlyDispatcher extends ContentDispatcher
{
    protected function getActionEventBaseName(): string
    {
        return ContentOnTheFlyEvents::CONTENT_CREATE;
    }
}
