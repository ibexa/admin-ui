<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlRemoveData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\Bundle\AdminUi\Controller\UrlAliasController;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @covers \Ibexa\Bundle\AdminUi\Controller\UrlAliasController
 */
final class UrlAliasControllerTest extends TestCase
{
    private const CUSTOM_URL_ALIAS_ID = '42-0d3f1a8c2e6b4d7f9a1c3e5b7d9f1a3c';

    /**
     * Regression test for IBX-11423: custom URL aliases created in a language which is not
     * in the prioritized languages list must be loaded regardless of the current language
     * settings, otherwise they can't be removed.
     */
    public function testRemoveActionLoadsAliasesInAllTranslations(): void
    {
        $location = new Location(['id' => 2, 'contentInfo' => new ContentInfo(['id' => 1])]);
        $urlAlias = new URLAlias([
            'id' => self::CUSTOM_URL_ALIAS_ID,
            'languageCodes' => ['nor-NO'],
            'isCustom' => true,
        ]);

        $urlAliasService = $this->createMock(URLAliasService::class);
        $urlAliasService
            ->expects(self::once())
            ->method('load')
            ->with(self::CUSTOM_URL_ALIAS_ID, true)
            ->willReturn($urlAlias);
        $urlAliasService
            ->expects(self::once())
            ->method('removeAliases')
            ->with([$urlAlias]);

        $controller = new UrlAliasController(
            $this->createFormFactory(
                new CustomUrlRemoveData($location, [self::CUSTOM_URL_ALIAS_ID => true])
            ),
            $this->createSubmitHandler(),
            $urlAliasService
        );
        $controller->setContainer($this->createContainer());

        $response = $controller->removeAction(new Request());

        self::assertInstanceOf(RedirectResponse::class, $response);
    }

    private function createFormFactory(CustomUrlRemoveData $data): FormFactory
    {
        $form = $this->createStub(FormInterface::class);
        $form->method('getData')->willReturn($data);
        $form->method('isSubmitted')->willReturn(true);

        $formFactory = $this->createStub(FormFactory::class);
        $formFactory->method('removeCustomUrl')->willReturn($form);

        return $formFactory;
    }

    private function createSubmitHandler(): SubmitHandler
    {
        $submitHandler = $this->createStub(SubmitHandler::class);
        $submitHandler
            ->method('handle')
            ->willReturnCallback(
                static fn (FormInterface $form, callable $handler): ?Response => $handler($form->getData())
            );

        return $submitHandler;
    }

    private function createContainer(): Container
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturn('/view/content/1/full/1/2#ibexa-tab-location-view-urls');

        $container = new Container();
        $container->set('router', $router);

        return $container;
    }
}
