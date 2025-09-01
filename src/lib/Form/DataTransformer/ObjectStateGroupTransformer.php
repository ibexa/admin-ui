<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Translates ObjectStateGroup's ID to domain specific ObjectStateGroup object.
 */
final readonly class ObjectStateGroupTransformer implements DataTransformerInterface
{
    public function __construct(private ObjectStateService $objectStateService)
    {
    }

    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ObjectStateGroup) {
            throw new TransformationFailedException('Expected a ' . ObjectStateGroup::class . ' object.');
        }

        return $value->id;
    }

    public function reverseTransform(mixed $value): ?ObjectStateGroup
    {
        if (empty($value)) {
            return null;
        }

        try {
            return $this->objectStateService->loadObjectStateGroup((int)$value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
