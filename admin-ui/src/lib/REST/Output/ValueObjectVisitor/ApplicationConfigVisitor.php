<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor;

use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorRegistryInterface;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * @internal
 */
final class ApplicationConfigVisitor extends ValueObjectVisitor
{
    private ApplicationConfigRestGeneratorRegistryInterface $applicationConfigRestGeneratorRegistry;

    public function __construct(ApplicationConfigRestGeneratorRegistryInterface $applicationConfigRestGeneratorRegistry)
    {
        $this->applicationConfigRestGeneratorRegistry = $applicationConfigRestGeneratorRegistry;
    }

    /**
     * @param \Ibexa\AdminUi\REST\Value\ApplicationConfig $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('ApplicationConfig');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ApplicationConfig'));

        foreach ($data->getConfig() as $namespace => $config) {
            // Checks if namespace has internal generators to generate custom output.
            if ($this->applicationConfigRestGeneratorRegistry->hasGenerators($namespace)) {
                $this->visitInternalGenerator(
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
     * @param array<string, mixed> $config
     */
    private function visitInternalGenerator(
        Visitor $visitor,
        Generator $generator,
        string $namespace,
        array $config
    ): void {
        $generator->startHashElement($namespace);

        foreach ($config as $name => $value) {
            if (!$this->applicationConfigRestGeneratorRegistry->hasGenerator($namespace, $name)) {
                $generator->generateFieldTypeHash($name, $value);

                continue;
            }

            $this->applicationConfigRestGeneratorRegistry
                ->getGenerator($namespace, $name)
                ->generate($value, $generator, $visitor);
        }

        $generator->endHashElement($namespace);
    }
}
