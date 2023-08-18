<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\Bundle\AdminUi\Templating\Twig\EmbeddedItemEditFormExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;
use Twig\Test\IntegrationTestCase;

/**
 * @covers \Ibexa\Bundle\AdminUi\Templating\Twig\EmbeddedItemEditFormExtension
 */
final class EmbeddedItemEditFormExtensionTest extends IntegrationTestCase
{
    private const FORM_ACTION = '/admin/content/edit';

    protected function getExtensions(): array
    {
        return [
            new EmbeddedItemEditFormExtension(
                $this->createFormFactory(),
                $this->createRouter()
            ),
        ];
    }

    /**
     * @dataProvider getLegacyTests
     * @group legacy
     *
     * @param string $file
     * @param string $message
     * @param string $condition
     * @param array<string> $templates
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

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/_fixtures/render_embedded_item_edit_form/';
    }

    private function createEditForm(): FormInterface
    {
        $editForm = $this->createMock(FormInterface::class);
        $editForm
            ->method('createView')
            ->willReturn(
                $this->createMock(FormView::class)
            );

        return $editForm;
    }

    private function createFormFactory(): FormFactory
    {
        $formFactory = $this->createMock(FormFactory::class);
        $formFactory
            ->method('contentEdit')
            ->with(
                new ContentEditData(),
                'embedded_item_edit',
                [
                    'action' => self::FORM_ACTION,
                    'attr' => [
                        'class' => 'ibexa-embedded-item-edit',
                    ],
                ]
            )
            ->willReturn($this->createEditForm());

        return $formFactory;
    }

    private function createRouter(): RouterInterface
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with('ibexa.content.edit')
            ->willReturn(self::FORM_ACTION);

        return $router;
    }
}
