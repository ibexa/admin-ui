<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\Templating\Twig\Components;

use Ibexa\Bundle\AdminUi\Templating\Twig\Components\Table;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\ChainConfigResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\UX\TwigComponent\Event\PreMountEvent;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

final class TableTest extends IbexaKernelTestCase
{
    use InteractsWithTwigComponents;

    protected function setUp(): void
    {
        self::bootKernel();

        $siteAccessService = self::getServiceByClassName(SiteAccessServiceInterface::class);
        assert($siteAccessService instanceof SiteAccessAware);
        $siteAccess = $siteAccessService->get('admin');

        $configResolver = self::getServiceByClassName(ConfigResolverInterface::class);
        self::assertInstanceOf(ChainConfigResolver::class, $configResolver);

        foreach ($configResolver->getAllResolvers() as $resolver) {
            if ($resolver instanceof SiteAccessAware) {
                $resolver->setSiteAccess($siteAccess);
            }
        }
    }

    public function testTableComponentMounts(): void
    {
        $component = $this->mountTwigComponent(
            name: 'ibexa.Table',
            data: [
                'data' => [],
            ],
        );

        self::assertInstanceOf(Table::class, $component);
    }

    public function testTableComponentRenders(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'ibexa.Table',
            data: [
                'data' => [],
            ],
        );

        self::assertStringContainsString('ibexa-table', $rendered->toString());
    }

    public function testTableComponentInfersDataType(): void
    {
        $component = $this->mountTwigComponent(
            name: 'ibexa.Table',
            data: [
                'data' => [new \stdClass(), new \stdClass()],
            ],
        );

        self::assertInstanceOf(Table::class, $component);

        $reflection = new \ReflectionProperty(Table::class, 'dataType');
        $reflection->setAccessible(true);

        self::assertSame(\stdClass::class, $reflection->getValue($component));
    }

    public function testTableComponentRendersEmptyState(): void
    {
        $rendered = $this->renderTwigComponent(
            name: 'ibexa.Table',
            data: [
                'data' => [],
                'emptyStateTitle' => new TranslatableMessage('Custom Title', [], 'messages'),
                'emptyStateDescription' => new TranslatableMessage('Custom Description', [], 'messages'),
            ],
        );

        $html = $rendered->toString();
        self::assertStringContainsString('ibexa-empty-state', $html);
        self::assertStringContainsString('Custom Title', $html);
        self::assertStringContainsString('Custom Description', $html);
    }

    public function testTableComponentRespectsPreMountEvent(): void
    {
        $dispatcher = self::getServiceByClassName(EventDispatcherInterface::class);
        $listener = static function (PreMountEvent $event): void {
            if (!$event->getComponent() instanceof Table) {
                return;
            }

            $data = $event->getData();
            $data['emptyStateTitle'] = new TranslatableMessage('Overridden Title', [], 'messages');
            $data['emptyStateDescription'] = new TranslatableMessage('Overridden Description', [], 'messages');
            $event->setData($data);
        };
        $dispatcher->addListener(PreMountEvent::class, $listener);

        try {
            $rendered = $this->renderTwigComponent(
                name: 'ibexa.Table',
                data: [
                    'data' => [],
                    'emptyStateTitle' => new TranslatableMessage('Original Title', [], 'ibexa_search'),
                    'emptyStateDescription' => new TranslatableMessage('Original Description', [], 'ibexa_search'),
                ],
            );

            $html = $rendered->toString();
            self::assertStringContainsString('Overridden Title', $html);
            self::assertStringNotContainsString('Original Title', $html);
            self::assertStringContainsString('Overridden Description', $html);
            self::assertStringNotContainsString('Original Description', $html);
        } finally {
            $dispatcher->removeListener(PreMountEvent::class, $listener);
        }
    }
}
