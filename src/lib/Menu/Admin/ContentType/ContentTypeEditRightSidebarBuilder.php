<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Menu\Admin\ContentType;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Section Edit contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class ContentTypeEditRightSidebarBuilder extends AbstractContentTypeRightSidebarBuilder implements TranslationContainerInterface
{
    /* Menu items */
    public const ITEM__SAVE = 'content_type_edit__sidebar_right__save';
    public const ITEM__PUBLISH_AND_EDIT = 'content_type_edit__sidebar_right__publish_and_edit';
    public const ITEM__CANCEL = 'content_type_edit__sidebar_right__cancel';

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_TYPE_EDIT_SIDEBAR_RIGHT;
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__SAVE, 'menu'))->setDesc('Save and close'),
            (new Message(self::ITEM__PUBLISH_AND_EDIT, 'menu'))->setDesc('Save'),
            (new Message(self::ITEM__CANCEL, 'menu'))->setDesc('Delete draft'),
        ];
    }

    public function getItemSaveIdentifier(): string
    {
        return self::ITEM__SAVE;
    }

    public function getItemPublishAndEditIdentifier(): string
    {
        return self::ITEM__PUBLISH_AND_EDIT;
    }

    public function getItemCancelIdentifier(): string
    {
        return self::ITEM__CANCEL;
    }
}

class_alias(ContentTypeEditRightSidebarBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\Admin\ContentType\ContentTypeEditRightSidebarBuilder');
