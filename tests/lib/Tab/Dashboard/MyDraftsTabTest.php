<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\Dashboard;

use Ibexa\AdminUi\Tab\Dashboard\MyDraftsTab;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @covers \Ibexa\AdminUi\Tab\Dashboard\MyDraftsTab
 */
final class MyDraftsTabTest extends TestCase
{
    public function testRenderView(): void
    {
        $templateMock = '{{ pager_options.routeName }} | {{ pager_options.pageParameter }}';
        $twigStub = new Environment(
            new ArrayLoader(
                [
                    '@ibexadesign/ui/dashboard/tab/my_draft_list.html.twig' => $templateMock,
                ]
            )
        );

        $requestStackMock = $this->createMock(RequestStack::class);
        $configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $tab = new MyDraftsTab(
            $twigStub,
            $this->createMock(TranslatorInterface::class),
            $this->createMock(ContentService::class),
            $this->createMock(ContentTypeService::class),
            $this->createMock(PermissionResolver::class),
            $this->createMock(DatasetFactory::class),
            $requestStackMock,
            $configResolverMock
        );

        $configResolverMock->method('getParameter')->with('pagination.content_draft_limit')->willReturn(10);
        $requestStackMock->method('getCurrentRequest')->willReturn(new Request());

        self::assertSame(
            // see $templateMock
            'app.page | [mydrafts-page]',
            $tab->renderView(
                [
                    'pager_options' => [
                        'routeName' => 'app.page',
                    ],
                ]
            )
        );
    }
}
