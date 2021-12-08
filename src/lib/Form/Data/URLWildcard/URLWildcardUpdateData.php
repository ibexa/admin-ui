<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\URLWildcard;

use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;

class URLWildcardUpdateData extends URLWildcardData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard|null */
    private $urlWildcard;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard|null $urlWildcard
     */
    public function __construct(?URLWildcard $urlWildcard = null)
    {
        if ($urlWildcard instanceof URLWildcard) {
            parent::__construct($urlWildcard);
            $this->urlWildcard = $urlWildcard;
        }
    }

    /** @return \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard|null */
    public function getUrlWildcard(): ?URLWildcard
    {
        return $this->urlWildcard;
    }

    /** @param \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard|null $urlWildcard */
    public function setUrlWildcard(?URLWildcard $urlWildcard): void
    {
        $this->urlWildcard = $urlWildcard;
    }
}

class_alias(URLWildcardUpdateData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\URLWildcard\URLWildcardUpdateData');
