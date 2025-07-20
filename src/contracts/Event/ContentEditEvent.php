<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
final class ContentEditEvent extends Event
{
    private ?Response $response = null;

    private Content $content;

    private VersionInfo $versionInfo;

    private string $languageCode;

    public function __construct(
        Content $content,
        VersionInfo $versionInfo,
        string $languageCode
    ) {
        $this->content = $content;
        $this->versionInfo = $versionInfo;
        $this->languageCode = $languageCode;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
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
        return !empty($this->response);
    }
}
