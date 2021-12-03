<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;

/**
 * Provides information about mapping between serialized sort order and the value accepted by sort clause.
 *
 * @see \Ibexa\Contracts\Rest\Output\ValueObjectVisitor::serializeSortOrder
 */
class SortOrderMappings implements ProviderInterface
{
    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'ASC' => Query::SORT_ASC,
            'DESC' => Query::SORT_DESC,
        ];
    }
}

class_alias(SortOrderMappings::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\SortOrderMappings');
