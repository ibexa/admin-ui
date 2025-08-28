<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData;
use Ibexa\Contracts\AdminUi\Form\DataMapper\DataMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Repository\Values\User\PolicyCreateStruct;

/**
 * Maps between PolicyCreateStruct and LanguageCreateData objects.
 */
final readonly class PolicyCreateMapper implements DataMapperInterface
{
    /**
     * Maps given PolicyCreateStruct object to a PolicyCreateData object.
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function map(ValueObject|PolicyCreateStruct $value): PolicyCreateData
    {
        if (!$value instanceof PolicyCreateStruct) {
            throw new InvalidArgumentException(
                'value',
                'must be an instance of ' . PolicyCreateStruct::class
            );
        }

        $data = new PolicyCreateData();

        $data->setModule($value->module);
        $data->setFunction($value->function);
        $data->setLimitations($value->getLimitations());

        return $data;
    }

    public function reverseMap(mixed $data): PolicyCreateStruct
    {
        if (!$data instanceof PolicyCreateData) {
            throw new InvalidArgumentException('data', 'must be an instance of ' . PolicyCreateData::class);
        }

        $policyCreateStruct = new PolicyCreateStruct([
            'module' => $data->getModule(),
            'function' => $data->getFunction(),
        ]);

        foreach ($data->getLimitations() as $limitation) {
            if (empty($limitation->limitationValues)) {
                continue;
            }

            $policyCreateStruct->addLimitation($limitation);
        }

        return $policyCreateStruct;
    }
}
