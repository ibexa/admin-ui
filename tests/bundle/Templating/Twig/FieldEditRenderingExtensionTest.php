<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Bundle\AdminUi\Templating\Twig\FieldEditRenderingExtension;
use Ibexa\Core\MVC\Symfony\Templating\Twig\FieldBlockRenderer;
use Ibexa\Core\MVC\Symfony\Templating\Twig\ResourceProviderInterface;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Tests\Core\MVC\Symfony\Templating\Twig\Extension\FileSystemTwigIntegrationTestCase;
use Twig\Environment;

final class FieldEditRenderingExtensionTest extends FileSystemTwigIntegrationTestCase
{
    private const int EXAMPLE_FIELD_DEFINITION_ID = 1;

    /**
     * @return \Twig\Extension\ExtensionInterface[]
     */
    public function getExtensions(): array
    {
        $resourceProvider = $this->createMock(ResourceProviderInterface::class);
        $resourceProvider->method('getFieldDefinitionEditResources')->willReturn([
            [
                'template' => $this->getTemplatePath('fields_override1.html.twig'),
                'priority' => 10,
            ],
            [
                'template' => $this->getTemplatePath('fields_default.html.twig'),
                'priority' => 0,
            ],
            [
                'template' => $this->getTemplatePath('fields_override2.html.twig'),
                'priority' => 20,
            ],
        ]);

        $fieldBlockRenderer = new FieldBlockRenderer(
            $this->createMock(Environment::class),
            $resourceProvider,
            $this->getTemplatePath('base.html.twig')
        );

        return [new FieldEditRenderingExtension($fieldBlockRenderer)];
    }

    protected static function getFixturesDirectory(): string
    {
        return __DIR__ . '/_fixtures/field_edit_rendering_functions/';
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function getFieldDefinitionData(
        string $typeIdentifier,
        int $id = self::EXAMPLE_FIELD_DEFINITION_ID,
        array $settings = []
    ): FieldDefinitionData {
        return new FieldDefinitionData([
            'fieldDefinition' => new FieldDefinition([
                'id' => $id,
                'fieldSettings' => $settings,
                'fieldTypeIdentifier' => $typeIdentifier,
            ]),
        ]);
    }

    /**
     * @dataProvider getLegacyTests
     *
     * @group legacy
     *
     * @param string $file
     * @param string $message
     * @param string $condition
     * @param array<mixed> $templates
     * @param string $exception
     * @param array<mixed> $outputs
     * @param string $deprecation
     */
    public function testLegacyIntegration(
        $file,
        $message,
        $condition,
        $templates,
        $exception,
        $outputs,
        $deprecation = ''
    ): void {
        // disable Twig legacy integration test to avoid producing risky warning
        self::markTestSkipped('This package does not contain Twig legacy integration test cases');
    }

    private function getTemplatePath(string $tpl): string
    {
        return 'templates/' . $tpl;
    }
}
