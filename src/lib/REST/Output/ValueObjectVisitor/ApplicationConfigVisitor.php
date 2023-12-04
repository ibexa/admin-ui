<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor;

use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverRegistryInterface;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

final class ApplicationConfigVisitor extends ValueObjectVisitor
{
    private ApplicationConfigRestResolverRegistryInterface $applicationConfigRestResolverRegistry;

    public function __construct(ApplicationConfigRestResolverRegistryInterface $applicationConfigRestResolverRegistry)
    {
        $this->applicationConfigRestResolverRegistry = $applicationConfigRestResolverRegistry;
    }

    /**
     * @param \Ibexa\AdminUi\REST\Value\ApplicationConfig $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('ApplicationConfig');

        foreach ($data->getConfig() as $namespace => $config) {
            if ($this->applicationConfigRestResolverRegistry->hasResolvers($namespace)) {
                $this->visitInternalResolver(
                    $visitor,
                    $generator,
                    $namespace,
                    $config
                );

                continue;
            }

            $generator->generateFieldTypeHash($namespace, $config);
        }

        $generator->endObjectElement('ApplicationConfig');
    }

    /**
     * @param array<mixed> $config
     */
    private function visitInternalResolver(
        Visitor $visitor,
        Generator $generator,
        string $namespace,
        array $config
    ): void {
        $generator->startHashElement($namespace);

        foreach ($config as $name => $value) {
            if (!$this->applicationConfigRestResolverRegistry->hasResolver($namespace, $name)) {
                $generator->generateFieldTypeHash($name, $value);

                continue;
            }

            $resolvedConfig = $this->applicationConfigRestResolverRegistry
                ->getResolver($namespace, $name)
                ->resolve($config);

            if (is_object($resolvedConfig)) {
                $generator->startHashElement($name);
                $visitor->visitValueObject($resolvedConfig);
                $generator->endHashElement($name);
            } else {
                $generator->generateFieldTypeHash($name, $resolvedConfig);
            }
        }

        $generator->endHashElement($namespace);
    }
}
