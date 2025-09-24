<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\URLWildcard;

use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;

final class URLWildcardUpdateData extends URLWildcardData
{
    public function __construct(private ?URLWildcard $urlWildcard = null)
    {
        if ($this->urlWildcard === null) {
            return;
        }

        parent::__construct($urlWildcard);
    }

    public function getUrlWildcard(): ?URLWildcard
    {
        return $this->urlWildcard;
    }

    public function setUrlWildcard(?URLWildcard $urlWildcard): void
    {
        $this->urlWildcard = $urlWildcard;
    }
}
