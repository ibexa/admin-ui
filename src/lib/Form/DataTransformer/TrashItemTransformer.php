<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TrashItemTransformer implements DataTransformerInterface
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\TrashService
     */
    private $trashService;

    public function __construct(TrashService $trashService)
    {
        $this->trashService = $trashService;
    }

    /**
     * Transforms a domain specific Trash Item object into a Trash Item's identifier.
     *
     * @param mixed $value
     *
     * @return mixed|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof TrashItem) {
            throw new TransformationFailedException('Expected a ' . TrashItem::class . ' object.');
        }

        return $value->id;
    }

    /**
     * Transforms a Trash Item's ID into a domain specific Trash Item object.
     *
     * @param mixed $value
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function reverseTransform($value): ?TrashItem
    {
        if (empty($value)) {
            return null;
        }

        if (!ctype_digit($value)) {
            throw new TransformationFailedException('Expected a numeric string.');
        }

        try {
            return $this->trashService->loadTrashItem((int)$value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

class_alias(TrashItemTransformer::class, 'EzSystems\EzPlatformAdminUi\Form\DataTransformer\TrashItemTransformer');
