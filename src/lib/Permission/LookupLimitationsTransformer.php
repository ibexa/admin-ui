<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;

/**
 * @internal
 */
final class LookupLimitationsTransformer
{
    /**
     * @return array<mixed>
     */
    public function getFlattenedLimitationsValues(LookupLimitationResult $lookupLimitations): array
    {
        $limitationsValues = [];

        foreach ($lookupLimitations->getRoleLimitations() as $roleLimitation) {
            $limitationsValues[] = $roleLimitation->limitationValues;
        }

        /** @var \Ibexa\Contracts\Core\Repository\Values\User\LookupPolicyLimitations $lookupPolicyLimitation */
        foreach ($lookupLimitations->getLookupPolicyLimitations() as $lookupPolicyLimitation) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation $limitation */
            foreach ($lookupPolicyLimitation->limitations as $limitation) {
                $limitationsValues[] = $limitation->limitationValues;
            }
        }

        return !empty($limitationsValues) ? array_unique(array_merge(...$limitationsValues)) : $limitationsValues;
    }

    /**
     * @param string[] $limitationsIdentifiers
     *
     * @return array<string, mixed>
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
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

        foreach ($lookupLimitations->getRoleLimitations() as $roleLimitation) {
            if (in_array($roleLimitation->getIdentifier(), $limitationsIdentifiers, true)) {
                $groupedLimitationsValues[$roleLimitation->getIdentifier()][] = $roleLimitation->limitationValues;
            }
        }

        foreach ($lookupLimitations->getLookupPolicyLimitations() as $lookupPolicyLimitation) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation $limitation */
            foreach ($lookupPolicyLimitation->limitations as $limitation) {
                if (in_array($limitation->getIdentifier(), $limitationsIdentifiers, true)) {
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
