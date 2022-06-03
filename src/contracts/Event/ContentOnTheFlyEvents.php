<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

final class ContentOnTheFlyEvents
{
    /** @var string */
    public const CONTENT_CREATE = 'ibexa.content_on_the_fly.create';

    /** @var string */
    public const CONTENT_CREATE_PUBLISH = 'ibexa.content_on_the_fly.create.publish';

    /** @var string */
    public const CONTENT_EDIT = 'ibexa.content_on_the_fly.edit';

    /** @var string */
    public const CONTENT_EDIT_PUBLISH = 'ibexa.content_on_the_fly.edit.publish';
}

class_alias(ContentOnTheFlyEvents::class, 'EzSystems\EzPlatformAdminUi\Event\ContentOnTheFlyEvents');
