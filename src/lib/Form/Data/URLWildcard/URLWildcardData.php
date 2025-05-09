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
    /** @var string|null */
    private ?string $destinationUrl = null;

    /** @var string|null */
    private ?string $sourceURL = null;

    /** @var bool */
    private $forward = false;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard|null $urlWildcard
     */
    public function __construct(?URLWildcard $urlWildcard = null)
    {
        if ($urlWildcard instanceof URLWildcard) {
            $this->destinationUrl = $urlWildcard->destinationUrl;
            $this->sourceURL = $urlWildcard->sourceUrl;
            $this->forward = $urlWildcard->forward;
        }
    }

    /**
     * @return string|null
     */
    public function getDestinationUrl(): ?string
    {
        return $this->destinationUrl;
    }

    /**
     * @param string $destinationUrl
     */
    public function setDestinationUrl(string $destinationUrl): void
    {
        $this->destinationUrl = $destinationUrl;
    }

    /**
     * @return string|null
     */
    public function getSourceURL(): ?string
    {
        return $this->sourceURL;
    }

    /**
     * @param string $sourceURL
     */
    public function setSourceURL(string $sourceURL): void
    {
        $this->sourceURL = $sourceURL;
    }

    /**
     * @return bool
     */
    public function getForward(): ?bool
    {
        return $this->forward;
    }

    /**
     * @param bool $forward
     */
    public function setForward(bool $forward): void
    {
        $this->forward = $forward;
    }
}
