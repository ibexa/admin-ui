<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Menu;

use Ibexa\AdminUi\Menu\ContentEditAnchorMenuBuilder;
use Ibexa\AdminUi\Menu\MenuItemFactory;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[CoversClass(ContentEditAnchorMenuBuilder::class)]
final class ContentEditAnchorMenuBuilderTest extends TestCase
{
    public function testFieldGroupItemsAreLabelledWithGroupNames(): void
    {
        $fieldsGroupsList = self::createStub(FieldsGroupsList::class);
        $fieldsGroupsList->method('getGroups')->willReturn(['content' => 'Inhalt']);

        $configResolver = self::createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturn([]);

        $builder = new ContentEditAnchorMenuBuilder(
            new MenuItemFactory(new MenuFactory(), self::createStub(LocationService::class)),
            self::createStub(EventDispatcherInterface::class),
            $configResolver,
            $fieldsGroupsList,
        );

        $menu = $builder->build([
            'content_type' => new ContentType(['fieldDefinitions' => new FieldDefinitionCollection()]),
            'grouped_fields' => [
                'content' => ['name'],
                'billing_address' => ['street'],
            ],
        ]);

        $groupItems = $menu->getChild(ContentEditAnchorMenuBuilder::ITEM__CONTENT)?->getChildren();
        self::assertNotNull($groupItems);

        self::assertSame('Inhalt', $groupItems['content']->getLabel());
        self::assertSame(
            'ibexa-edit-content-sections-content-fields-content',
            $groupItems['content']->getAttribute('data-target-id')
        );

        // a group missing from the configured list falls back to its identifier
        self::assertSame('billing_address', $groupItems['billing_address']->getLabel());
        self::assertSame(
            'ibexa-edit-content-sections-content-fields-billing-address',
            $groupItems['billing_address']->getAttribute('data-target-id')
        );
    }
}
