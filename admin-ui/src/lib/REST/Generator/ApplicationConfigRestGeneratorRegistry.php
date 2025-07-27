<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Generator;

use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorInterface;
use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorRegistryInterface;
use RuntimeException;

final class ApplicationConfigRestGeneratorRegistry implements ApplicationConfigRestGeneratorRegistryInterface
{
    /** @var iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorInterface> */
    private iterable $generators;

    /**
     * @param iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorInterface> $generators
     */
    public function __construct(iterable $generators)
    {
        $this->generators = $generators;
    }

    public function hasGenerator(
        string $namespace,
        string $parameter
    ): bool {
        foreach ($this->generators as $generator) {
            if (
                $generator->supportsNamespace($namespace)
                && $generator->supportsParameter($parameter)
            ) {
                return true;
            }
        }

        return false;
    }

    public function hasGenerators(string $namespace): bool
    {
        foreach ($this->generators as $generator) {
            if ($generator->supportsNamespace($namespace)) {
                return true;
            }
        }

        return false;
    }

    public function getGenerator(string $namespace, string $parameter): ApplicationConfigRestGeneratorInterface
    {
        foreach ($this->generators as $generator) {
            if (
                $generator->supportsNamespace($namespace)
                && $generator->supportsParameter($parameter)
            ) {
                return $generator;
            }
        }

        throw new RuntimeException(
            sprintf(
                'No Generator found for Configuration in namespace \'%s\' and parameter \'%s\'',
                $namespace,
                $parameter
            )
        );
    }

    /**
     * @return iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorInterface>
     */
    public function getGenerators(string $namespace): iterable
    {
        foreach ($this->generators as $generator) {
            if ($generator->supportsNamespace($namespace)) {
                yield $generator;
            }
        }

        throw new RuntimeException(
            'No Generators found for Configuration in namespace \'' . $namespace . '\'',
        );
    }
}
