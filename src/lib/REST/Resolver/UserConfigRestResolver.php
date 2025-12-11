<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Resolver;

use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

final readonly class UserConfigRestResolver implements ApplicationConfigRestResolverInterface
{
    public const string NAMESPACE = 'user';
    public const string PARAMETER = 'user';

    public function resolve(array $config): ?ValueObject
    {
        return $config['user'];
    }

    public function supportsNamespace(string $namespace): bool
    {
        return self::NAMESPACE === $namespace;
    }

    public function supportsParameter(string $parameterName): bool
    {
        return self::PARAMETER === $parameterName;
    }
}
