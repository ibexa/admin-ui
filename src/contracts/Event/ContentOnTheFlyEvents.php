<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

final readonly class ContentOnTheFlyEvents
{
    public const string CONTENT_CREATE = 'ibexa.content_on_the_fly.create';

    public const string CONTENT_CREATE_PUBLISH = 'ibexa.content_on_the_fly.create.publish';

    public const string CONTENT_EDIT = 'ibexa.content_on_the_fly.edit';

    public const string CONTENT_EDIT_PUBLISH = 'ibexa.content_on_the_fly.edit.publish';
}
