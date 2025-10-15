<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final readonly class UserContentTypes implements ProviderInterface
{
    public function __construct(private ConfigResolverInterface $configResolver)
    {
    }

    public function getConfig(): mixed
    {
        return $this->configResolver->getParameter(
            'user_content_type_identifier'
        );
    }
}
