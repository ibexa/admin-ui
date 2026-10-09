<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Ibexa\Contracts\Core\Repository\Values\User\LookupPolicyLimitations;

/**
 * @internal
 */
final class LookupLimitationsTransformer
{
    /**
     * @param LookupLimitationResult $lookupLimitations
     *
     * @return array
     */
    public function getFlattenedLimitationsValues(LookupLimitationResult $lookupLimitations): array
    {
        $limitationsValues = [];

        foreach ($lookupLimitations->roleLimitations as $roleLimitation) {
            $limitationsValues[] = $roleLimitation->limitationValues;
        }

        /** @var LookupPolicyLimitations $lookupPolicyLimitation */
        foreach ($lookupLimitations->lookupPolicyLimitations as $lookupPolicyLimitation) {
            /** @var Limitation $limitation */
            foreach ($lookupPolicyLimitation->limitations as $limitation) {
                $limitationsValues[] = $limitation->limitationValues;
            }
        }

        return !empty($limitationsValues) ? array_unique(array_merge(...$limitationsValues)) : $limitationsValues;
    }

    /**
     * @param LookupLimitationResult $lookupLimitations
     * @param string[] $limitationsIdentifiers
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function getGroupedLimitationValues(
        LookupLimitationResult $lookupLimitations,
        array $limitationsIdentifiers
    ): array {
        if (empty($limitationsIdentifiers)) {
            throw new InvalidArgumentException('limitationsIdentifiers', 'must contain at least one Limitation identifier');
        }
        $groupedLimitationsValues = [];

        foreach ($limitationsIdentifiers as $limitationsIdentifier) {
            $groupedLimitationsValues[$limitationsIdentifier] = [];
        }

        foreach ($lookupLimitations->roleLimitations as $roleLimitation) {
            if (\in_array($roleLimitation->getIdentifier(), $limitationsIdentifiers, true)) {
                $groupedLimitationsValues[$roleLimitation->getIdentifier()][] = $roleLimitation->limitationValues;
            }
        }

        foreach ($lookupLimitations->lookupPolicyLimitations as $lookupPolicyLimitation) {
            /** @var Limitation $limitation */
            foreach ($lookupPolicyLimitation->limitations as $limitation) {
                if (\in_array($limitation->getIdentifier(), $limitationsIdentifiers, true)) {
                    $groupedLimitationsValues[$limitation->getIdentifier()][] = $limitation->limitationValues;
                }
            }
        }

        foreach ($groupedLimitationsValues as $identifier => $limitationsValues) {
            if (!empty($limitationsValues)) {
                $groupedLimitationsValues[$identifier] = array_unique(array_merge(...$limitationsValues));
            }
        }

        return $groupedLimitationsValues;
    }
}

class_alias(LookupLimitationsTransformer::class, 'EzSystems\EzPlatformAdminUi\Permission\LookupLimitationsTransformer');
