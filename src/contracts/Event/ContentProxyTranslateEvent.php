<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

use Ibexa\AdminUi\Event\Options;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
class ContentProxyTranslateEvent extends Event
{
    private ?Response $response = null;

    private Options $options;

    public function __construct(
        private readonly int $contentId,
        private readonly ?string $fromLanguageCode,
        private readonly string $toLanguageCode,
        ?Options $options = null,
        private readonly ?int $locationId = null
    ) {
        $this->options = $options ?? new Options();
    }

    public function getContentId(): int
    {
        return $this->contentId;
    }

    public function getFromLanguageCode(): ?string
    {
        return $this->fromLanguageCode;
    }

    public function getToLanguageCode(): string
    {
        return $this->toLanguageCode;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function getLocationId(): ?int
    {
        return $this->locationId;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function hasResponse(): bool
    {
        return isset($this->response);
    }
}
