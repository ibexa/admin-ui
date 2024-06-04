<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Translates ObjectState's ID to domain specific ObjectState object.
 */
class ObjectStateTransformer implements DataTransformerInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ObjectStateService */
    protected $objectStateService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ObjectStateService $objectStateService
     */
    public function __construct(ObjectStateService $objectStateService)
    {
        $this->objectStateService = $objectStateService;
    }

    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ObjectState) {
            throw new TransformationFailedException('Expected a ' . ObjectState::class . ' object.');
        }

        return $value->id;
    }

    public function reverseTransform($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return $this->objectStateService->loadObjectState($value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
