<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Form\Data\Policy\PolicyUpdateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Repository\Values\User\PolicyUpdateStruct;

/**
 * Maps between PolicyUpdateStruct and PolicyUpdateData objects.
 */
class PolicyUpdateMapper implements DataMapperInterface
{
    /**
     * Maps given PolicyUpdateStruct object to a PolicyUpdateData object.
     *
     * @param PolicyUpdateStruct|ValueObject $value
     *
     * @return PolicyUpdateData
     */
    public function map(ValueObject $value): PolicyUpdateData
    {
        $data = new PolicyUpdateData();

        $data->setModule($value->module);
        $data->setFunction($value->function);
        $data->setLimitations($value->getLimitations());

        return $data;
    }

    /**
     * Maps given PolicyUpdateData object to a PolicyUpdateStruct object.
     *
     * @param PolicyUpdateData $data
     *
     * @return PolicyUpdateStruct
     */
    public function reverseMap($data): PolicyUpdateStruct
    {
        $policyUpdateStruct = new PolicyUpdateStruct();

        foreach ($data->getLimitations() as $limitation) {
            if (!empty($limitation->limitationValues)) {
                $policyUpdateStruct->addLimitation($limitation);
            }
        }

        return $policyUpdateStruct;
    }
}

class_alias(PolicyUpdateMapper::class, 'EzSystems\EzPlatformAdminUi\Form\DataMapper\PolicyUpdateMapper');
