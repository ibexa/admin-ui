<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Menu\Admin\ContentType;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use JMS\TranslationBundle\Model\Message;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Section Edit contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class ContentTypeCreateRightSidebarBuilder extends AbstractContentTypeRightSidebarBuilder
{
    /* Menu items */
    public const ITEM__SAVE = 'content_type_create__sidebar_right__save';
    public const ITEM__CANCEL = 'content_type_create__sidebar_right__cancel';

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_TYPE_CREATE_SIDEBAR_RIGHT;
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__SAVE, 'menu'))->setDesc('Create'),
            (new Message(self::ITEM__CANCEL, 'menu'))->setDesc('Cancel'),
        ];
    }

    public function getItemSaveIdentifier(): string
    {
        return self::ITEM__SAVE;
    }

    public function getItemCancelIdentifier(): string
    {
        return self::ITEM__CANCEL;
    }
}

class_alias(ContentTypeCreateRightSidebarBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\Admin\ContentType\ContentTypeCreateRightSidebarBuilder');
