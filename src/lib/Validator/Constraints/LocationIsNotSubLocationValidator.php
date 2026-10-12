<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Validator\Constraints\AbstractComparisonValidator;

class LocationIsNotSubLocationValidator extends AbstractComparisonValidator
{
    /**
     * Compares the two given values to find if their relationship is valid.
     *
     * @param Location $targetLocation
     * @param Location $sourceLocation
     *
     * @return bool true if the relationship is valid, false otherwise
     */
    protected function compareValues(
        $targetLocation,
        $sourceLocation
    ) {
        return stripos($targetLocation->pathString, $sourceLocation->pathString) === false;
    }
}

class_alias(LocationIsNotSubLocationValidator::class, 'EzSystems\EzPlatformAdminUi\Validator\Constraints\LocationIsNotSubLocationValidator');
