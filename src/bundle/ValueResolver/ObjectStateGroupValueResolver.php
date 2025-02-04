<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup>
 */
final class ObjectStateGroupValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_OBJECT_STATE_GROUP_ID = 'objectStateGroupId';

    public function __construct(
        private readonly ObjectStateService $objectStateService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_OBJECT_STATE_GROUP_ID];
    }

    protected function getClass(): string
    {
        return ObjectStateGroup::class;
    }

    protected function validateValue(string $value): bool
    {
        return is_numeric($value);
    }

    protected function load(array $key): object
    {
        return $this->objectStateService->loadObjectStateGroup(
            (int)$key[self::ATTRIBUTE_OBJECT_STATE_GROUP_ID]
        );
    }
}
