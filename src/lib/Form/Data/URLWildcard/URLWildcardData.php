<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\URLWildcard;

use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;

class URLWildcardData
{
    private ?string $destinationUrl = null;

    private ?string $sourceURL = null;

    private bool $forward = false;

    public function __construct(?URLWildcard $urlWildcard = null)
    {
        if ($urlWildcard === null) {
            return;
        }

        $this->destinationUrl = $urlWildcard->destinationUrl;
        $this->sourceURL = $urlWildcard->sourceUrl;
        $this->forward = $urlWildcard->forward;
    }

    public function getDestinationUrl(): ?string
    {
        return $this->destinationUrl;
    }

    public function setDestinationUrl(?string $destinationUrl): void
    {
        $this->destinationUrl = $destinationUrl;
    }

    public function getSourceURL(): ?string
    {
        return $this->sourceURL;
    }

    public function setSourceURL(?string $sourceURL): void
    {
        $this->sourceURL = $sourceURL;
    }

    public function getForward(): bool
    {
        return $this->forward;
    }

    public function setForward(bool $forward): void
    {
        $this->forward = $forward;
    }
}
