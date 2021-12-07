<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\ActionDispatcher;

use Ibexa\ContentForms\Form\ActionDispatcher\ContentDispatcher;
use Ibexa\Contracts\AdminUi\Event\UserOnTheFlyEvents;

class CreateUserOnTheFlyDispatcher extends ContentDispatcher
{
    protected function getActionEventBaseName(): string
    {
        return UserOnTheFlyEvents::USER_CREATE;
    }
}

class_alias(CreateUserOnTheFlyDispatcher::class, 'EzSystems\EzPlatformAdminUi\Form\ActionDispatcher\CreateUserOnTheFlyDispatcher');
