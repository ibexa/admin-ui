<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

final readonly class UserOnTheFlyEvents
{
    public const string USER_CREATE = 'ibexa.user_on_the_fly.create';

    public const string USER_CREATE_PUBLISH = 'ibexa.user_on_the_fly.create.create';

    public const string USER_EDIT = 'ibexa.user_on_the_fly.edit';

    public const string USER_EDIT_PUBLISH = 'ibexa.user_on_the_fly.edit.update';
}
