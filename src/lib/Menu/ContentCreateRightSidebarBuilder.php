<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Content Edit contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
final class ContentCreateRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const string ITEM__PUBLISH = 'content_create__sidebar_right__publish';
    public const string ITEM__SAVE_DRAFT = 'content_create__sidebar_right__save_draft';
    public const string ITEM__SAVE_DRAFT_AND_CLOSE = 'content_create__sidebar_right__save_draft_and_close';
    public const string ITEM__PREVIEW = 'content_create__sidebar_right__preview';
    public const string ITEM__CANCEL = 'content_create__sidebar_right__cancel';

    public const string BTN_TRIGGER_CLASS = 'ibexa-btn--trigger';
    public const array BTN_DISABLED_ATTR = ['disabled' => 'disabled'];

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        private readonly PermissionResolver $permissionResolver,
        private readonly ContentService $contentService,
        private readonly LocationService $locationService
    ) {
        parent::__construct($factory, $eventDispatcher);
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_CREATE_SIDEBAR_RIGHT;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $parentLocation */
        $parentLocation = $options['parent_location'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $options['content_type'];
        $parentContentType = $parentLocation->getContent()->getContentType();
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language $language */
        $language = $options['language'];
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');

        $contentCreateStruct = $this->createContentCreateStruct($parentLocation, $contentType, $language);
        $locationCreateStruct = $this->locationService->newLocationCreateStruct($parentLocation->getId());

        $canPublish = $this->permissionResolver->canUser('content', 'publish', $contentCreateStruct, [$locationCreateStruct]);
        $canCreate = $this->permissionResolver->canUser('content', 'create', $contentCreateStruct, [$locationCreateStruct]) && $parentContentType->isContainer();
        $canPreview = $this->permissionResolver->canUser('content', 'versionread', $contentCreateStruct, [$locationCreateStruct]);

        $publishAttributes = [
            'class' => self::BTN_TRIGGER_CLASS,
            'data-click' => '#ezplatform_content_forms_content_edit_publish',
        ];
        $saveDraftAttributes = [
            'class' => self::BTN_TRIGGER_CLASS,
            'data-click' => '#ezplatform_content_forms_content_edit_saveDraft',
        ];
        $saveDraftAndCloseAttributes = [
            'class' => self::BTN_TRIGGER_CLASS,
            'data-click' => '#ezplatform_content_forms_content_edit_saveDraftAndClose',
        ];
        $previewAttributes = [
            'class' => self::BTN_TRIGGER_CLASS,
            'data-click' => '#ezplatform_content_forms_content_edit_preview',
        ];

        $menu->setChildren([
            self::ITEM__PUBLISH => $this->createMenuItem(
                self::ITEM__PUBLISH,
                [
                    'attributes' => $canCreate && $canPublish
                        ? $publishAttributes
                        : array_merge($publishAttributes, self::BTN_DISABLED_ATTR),
                    'extras' => [
                        'orderNumber' => 10,
                    ],
                ]
            ),
            self::ITEM__PREVIEW => $this->createMenuItem(
                self::ITEM__PREVIEW,
                [
                    'attributes' => $canPreview
                        ? $previewAttributes
                        : array_merge($previewAttributes, self::BTN_DISABLED_ATTR),
                    'extras' => [
                        'orderNumber' => 60,
                    ],
                ]
            ),
            self::ITEM__CANCEL => $this->createMenuItem(
                self::ITEM__CANCEL,
                [
                    'attributes' => [
                        'class' => self::BTN_TRIGGER_CLASS,
                        'data-click' => '#ezplatform_content_forms_content_edit_cancel',
                    ],
                    'extras' => [
                        'orderNumber' => 70,
                    ],
                ]
            ),
        ]);

        $saveDraftAndCloseItem = $this->createMenuItem(
            self::ITEM__SAVE_DRAFT_AND_CLOSE,
            [
                'attributes' => $canCreate
                    ? $saveDraftAndCloseAttributes
                    : array_merge($saveDraftAndCloseAttributes, self::BTN_DISABLED_ATTR),
                'extras' => [
                    'orderNumber' => 80,
                ],
            ]
        );

        $saveDraftAndCloseItem->addChild(
            self::ITEM__SAVE_DRAFT,
            [
                'attributes' => $canCreate
                    ? $saveDraftAttributes
                    : array_merge($saveDraftAttributes, self::BTN_DISABLED_ATTR),
                'extras' => [
                    'orderNumber' => 10,
                ],
            ]
        );

        $menu->addChild($saveDraftAndCloseItem);

        return $menu;
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__PUBLISH, 'ibexa_menu'))->setDesc('Publish'),
            (new Message(self::ITEM__SAVE_DRAFT, 'ibexa_menu'))->setDesc('Save'),
            (new Message(self::ITEM__SAVE_DRAFT_AND_CLOSE, 'ibexa_menu'))->setDesc('Save and close'),
            (new Message(self::ITEM__PREVIEW, 'ibexa_menu'))->setDesc('Preview'),
            (new Message(self::ITEM__CANCEL, 'ibexa_menu'))->setDesc('Cancel'),
        ];
    }

    private function createContentCreateStruct(
        Location $location,
        ContentType $contentType,
        Language $language
    ): ContentCreateStruct {
        $contentCreateStruct = $this->contentService->newContentCreateStruct(
            $contentType,
            $language->getLanguageCode()
        );

        $contentCreateStruct->sectionId = $location->getContentInfo()->getSectionId();

        return $contentCreateStruct;
    }
}
