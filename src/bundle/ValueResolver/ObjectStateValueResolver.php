<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState>
 */
final class ObjectStateValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_OBJECT_STATE_ID = 'objectStateId';

    public function __construct(
        private readonly ObjectStateService $objectStateService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_OBJECT_STATE_ID];
    }

    protected function getClass(): string
    {
        return ObjectState::class;
    }

    protected function load(array $key): object
    {
        return $this->objectStateService->loadObjectState(
            (int)$key[self::ATTRIBUTE_OBJECT_STATE_ID]
        );
    }
}
