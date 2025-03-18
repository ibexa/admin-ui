<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Event;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

final class CancelEditVersionDraftEvent extends Event
{
    private Content $content;

    private Location $referrerLocation;

    /** @var \Symfony\Component\HttpFoundation\Response|null */
    private ?Response $response = null;

    public function __construct(
        Content $content,
        Location $referrerLocation
    ) {
        $this->content = $content;
        $this->referrerLocation = $referrerLocation;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getReferrerLocation(): Location
    {
        return $this->referrerLocation;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}
