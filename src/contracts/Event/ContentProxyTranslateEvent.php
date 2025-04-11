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
    /** @var \Symfony\Component\HttpFoundation\Response|null */
    private ?Response $response = null;

    private int $contentId;

    private ?string $fromLanguageCode;

    private string $toLanguageCode;

    private Options $options;

    private ?int $locationId;

    public function __construct(
        int $contentId,
        ?string $fromLanguageCode,
        string $toLanguageCode,
        ?Options $options = null,
        ?int $locationId = null
    ) {
        $this->contentId = $contentId;
        $this->fromLanguageCode = $fromLanguageCode;
        $this->toLanguageCode = $toLanguageCode;
        $this->options = $options ?? new Options();
        $this->locationId = $locationId;
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
