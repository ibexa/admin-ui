<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

use Ibexa\AdminUi\Event\Options;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
class ContentProxyCreateEvent extends Event
{
    public const OPTION_CONTENT_DRAFT = 'contentDraft';
    public const OPTION_IS_ON_THE_FLY = 'isOnTheFly';

    /** @var \Symfony\Component\HttpFoundation\Response|null */
    private ?Response $response = null;

    private ContentType $contentType;

    private string $languageCode;

    private int $parentLocationId;

    private Options $options;

    public function __construct(
        ContentType $contentType,
        string $languageCode,
        int $parentLocationId,
        ?Options $options = null
    ) {
        $this->contentType = $contentType;
        $this->languageCode = $languageCode;
        $this->parentLocationId = $parentLocationId;
        $this->options = $options ?? new Options();
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getParentLocationId(): int
    {
        return $this->parentLocationId;
    }

    public function getOptions(): Options
    {
        return $this->options;
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
