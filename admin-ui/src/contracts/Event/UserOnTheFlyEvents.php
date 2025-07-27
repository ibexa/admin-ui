<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

final class UserOnTheFlyEvents
{
    /** @var string */
    public const USER_CREATE = 'ibexa.user_on_the_fly.create';

    /** @var string */
    public const USER_CREATE_PUBLISH = 'ibexa.user_on_the_fly.create.create';

    /** @var string */
    public const USER_EDIT = 'ibexa.user_on_the_fly.edit';

    /** @var string */
    public const USER_EDIT_PUBLISH = 'ibexa.user_on_the_fly.edit.update';
}
