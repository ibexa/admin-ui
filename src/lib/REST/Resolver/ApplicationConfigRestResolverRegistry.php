<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Resolver;

use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverInterface;
use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverRegistryInterface;

final class ApplicationConfigRestResolverRegistry implements ApplicationConfigRestResolverRegistryInterface
{
    /** @var iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverInterface> */
    private iterable $resolvers;

    /**
     * @param iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverInterface> $resolvers
     */
    public function __construct(iterable $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function hasResolver(
        string $namespace,
        string $parameter
    ): bool {
        foreach ($this->resolvers as $resolver) {
            if (
                $resolver->supportsNamespace($namespace)
                && $resolver->supportsParameter($parameter)
            ) {
                return true;
            }
        }

        return false;
    }

    public function hasResolvers(string $namespace): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supportsNamespace($namespace)) {
                return true;
            }
        }

        return false;
    }

    public function getResolver(string $namespace, string $parameter): ?ApplicationConfigRestResolverInterface
    {
        foreach ($this->resolvers as $resolver) {
            if ($this->hasResolver($namespace, $parameter)) {
                return $resolver;
            }
        }

        return null;
    }

    /**
     * @return iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverInterface>
     */
    public function getResolvers(string $namespace): iterable
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supportsNamespace($namespace)) {
                yield $resolver;
            }
        }
    }
}
