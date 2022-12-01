<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;

/**
 * Interface for Limitation Value mappers.
 */
interface LimitationValueMapperInterface
{
    /**
     * Map the limitation values, in order to pass them as context of limitation value rendering.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation $limitation
     *
     * @return mixed[]
     */
    public function mapLimitationValue(Limitation $limitation);
}

class_alias(
    LimitationValueMapperInterface::class,
    \EzSystems\RepositoryForms\Limitation\LimitationValueMapperInterface::class
);

class_alias(LimitationValueMapperInterface::class, 'EzSystems\EzPlatformAdminUi\Limitation\LimitationValueMapperInterface');
