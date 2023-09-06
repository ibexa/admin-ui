<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUserGroup;
use Ibexa\AdminUi\Specification\Location\IsRoot;
use Ibexa\AdminUi\Specification\Location\IsWithinCopySubtreeLimit;
use Ibexa\AdminUi\UniversalDiscovery\ConfigResolver;
use Ibexa\Bundle\AdminUi\Templating\Twig\UniversalDiscoveryExtension;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Limitation\Target\Builder\VersionBuilder;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Repository\UserService;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI Location View contextual sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class ContentRightSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    /* Menu items */
    public const ITEM__CREATE = 'content__sidebar_right__create';
    public const ITEM__PREVIEW = 'content__sidebar_right__preview';
    public const ITEM__EDIT = 'content__sidebar_right__edit';
    public const ITEM__SEND_TO_TRASH = 'content__sidebar_right__send_to_trash';
    public const ITEM__COPY = 'content__sidebar_right__copy';
    public const ITEM__COPY_SUBTREE = 'content__sidebar_right__copy_subtree';
    public const ITEM__MOVE = 'content__sidebar_right__move';
    public const ITEM__DELETE = 'content__sidebar_right__delete';
    public const ITEM__HIDE = 'content__sidebar_right__hide';
    public const ITEM__REVEAL = 'content__sidebar_right__reveal';
    public const ITEM__INVITE = 'content__sidebar_right__invite';

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Ibexa\AdminUi\UniversalDiscovery\ConfigResolver */
    private $udwConfigResolver;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Bundle\AdminUi\Templating\Twig\UniversalDiscoveryExtension */
    private $udwExtension;

    /** @var \Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface */
    private $permissionChecker;

    private LanguageService $languageService;

    private UrlGeneratorInterface $urlGenerator;

    private SiteaccessResolverInterface  $siteaccessResolver;

    private UserService $userService;

    private TranslatorInterface $translator;

    public function __construct(
        MenuItemFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        PermissionResolver $permissionResolver,
        ConfigResolver $udwConfigResolver,
        ConfigResolverInterface $configResolver,
        LocationService $locationService,
        UniversalDiscoveryExtension $udwExtension,
        PermissionCheckerInterface $permissionChecker,
        LanguageService $languageService,
        UrlGeneratorInterface $urlGenerator,
        SiteaccessResolverInterface $siteaccessResolver,
        UserService $userService,
        TranslatorInterface $translator
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
        $this->udwConfigResolver = $udwConfigResolver;
        $this->locationService = $locationService;
        $this->udwExtension = $udwExtension;
        $this->permissionChecker = $permissionChecker;
        $this->languageService = $languageService;
        $this->urlGenerator = $urlGenerator;
        $this->siteaccessResolver = $siteaccessResolver;
        $this->userService = $userService;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_SIDEBAR_RIGHT;
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function createStructure(array $options): ItemInterface
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
        $location = $options['location'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $options['content_type'];
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $options['content'];
        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->factory->createItem('root');
        $startingLocationId = $this->udwConfigResolver->getConfig('default')['starting_location_id'];

        $lookupLimitationsResult = $this->permissionChecker->getContentCreateLimitations($location);
        $canCreate = $lookupLimitationsResult->hasAccess && $contentType->isContainer;
        $uwdConfig = $this->udwExtension->renderUniversalDiscoveryWidgetConfig('single_container');
        $canEdit = $this->permissionResolver->canUser(
            'content',
            'edit',
            $location->getContentInfo(),
            [
                (new VersionBuilder())
                    ->translateToAnyLanguageOf($content->getVersionInfo()->languageCodes)
                    ->build(),
            ]
        );
        $translations = $content->getVersionInfo()->languageCodes;
        $target = (new Target\Version())->deleteTranslations($translations);
        $canDelete = $this->permissionResolver->canUser(
            'content',
            'remove',
            $content,
            [$target]
        );
        $canTrashLocation = $this->permissionResolver->canUser(
            'content',
            'remove',
            $location->getContentInfo(),
            [$location, $target]
        );
        $canHide = $this->permissionResolver->canUser(
            'content',
            'hide',
            $content,
            [$target]
        );
        $hasCreatePermission = $this->hasCreatePermission();
        $canCopy = $this->canCopy($hasCreatePermission);
        $canCopySubtree = $this->canCopySubtree($location, $hasCreatePermission);
        $createAttributes = [
            'class' => 'ibexa-btn--extra-actions ibexa-btn--create ibexa-btn--primary',
            'data-actions' => 'create',
            'data-focus-element' => '.ibexa-instant-filter__input',
        ];
        $sendToTrashAttributes = [
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#trash-location-modal',
        ];
        $copySubtreeAttributes = [
            'class' => 'ibexa-btn--udw-copy-subtree',
            'data-udw-config' => $uwdConfig,
            'data-root-location' => $startingLocationId,
        ];
        $moveAttributes = [
            'class' => 'ibexa-btn--udw-move',
            'data-udw-config' => $uwdConfig,
            'data-root-location' => $startingLocationId,
        ];
        $copyAttributes = [
            'class' => 'ibexa-btn--udw-copy',
            'data-udw-config' => $uwdConfig,
            'data-root-location' => $startingLocationId,
        ];

        $contentIsUser = (new ContentTypeIsUser($this->configResolver->getParameter('user_content_type_identifier')))
            ->isSatisfiedBy($contentType);
        $contentIsUserGroup = (new ContentTypeIsUserGroup($this->configResolver->getParameter('user_group_content_type_identifier')))
            ->isSatisfiedBy($contentType);

        $menu->setChildren([
            self::ITEM__CREATE => $this->createMenuItem(
                self::ITEM__CREATE,
                [
                    'extras' => ['icon' => 'create', 'orderNumber' => 10],
                    'attributes' => $canCreate
                        ? $createAttributes
                        : array_merge($createAttributes, ['disabled' => 'disabled']),
                ]
            ),
        ]);

        $currentUser = $this->userService->loadUser(
            $this->permissionResolver->getCurrentUserReference()->getUserId()
        );
        $currentUserAccount = $currentUser->getField('user_account');
        $currentUserLanguageCode = $currentUserAccount->getLanguageCode();
        $mainPreviewItemLanguageCode = in_array($currentUserLanguageCode, $translations)
            ? $currentUserLanguageCode
            : $content->contentInfo->mainLanguageCode;

        $mainPreviewItemLanguage = $this->languageService->loadLanguage($mainPreviewItemLanguageCode);
        $mainPreviewItemLabel = $this->translator->trans(
            /** @Desc("Preview (%languageCode%)") */
            'content__sidebar_right__preview',
            [
                '%languageCode%' => $mainPreviewItemLanguageCode,
            ],
            'menu'
        );
        $mainPreviewItemAlternativeBtnLabel = $this->translator->trans(
            /** @Desc("%languageName% (%languageCode%)") */
            'content__sidebar_right__preview.alternative.main_button.label',
            [
                '%languageName%' => $mainPreviewItemLanguage->getName(),
                '%languageCode%' => $mainPreviewItemLanguageCode,
            ],
            'menu'
        );
        $mainPreviewItemAlternativeBtnSublabel = $this->translator->trans(
            /** @Desc("Default") */
            'content__sidebar_right__preview.alternative.main_btn.sublabel',
            [],
            'menu'
        );
        $mainPreviewItemToggleBtnLabel = $this->translator->trans(
            /** @Desc("Preview") */
            'content__sidebar_right__preview.toggle_btn.sublabel',
            [],
            'menu'
        );

        $previewItem = $this->getContentPreviewItem(
            $location,
            $content,
            $mainPreviewItemLanguage,
            [
                'label' => $mainPreviewItemLabel,
                'extras' => ['orderNumber' => 12],
                'attributes' => [
                    'data-alternative-main-btn-label' => $mainPreviewItemAlternativeBtnLabel,
                    'data-alternative-main-btn-sublabel' => $mainPreviewItemAlternativeBtnSublabel,
                    'data-alternative-toggle-label' => $mainPreviewItemToggleBtnLabel,
                ],
            ],
        );

        foreach ($translations as $languageCode) {
            if ($languageCode === $mainPreviewItemLanguageCode) {
                continue;
            }

            $language = $this->languageService->loadLanguage($languageCode);
            $subPreviewItemLabel = $this->translator->trans(
                /** @Desc("%languageName% (%languageCode%)") */
                'content__sidebar_right__preview.subpreview',
                [
                    '%languageName%' => $language->getName(),
                    '%languageCode%' => $languageCode,
                ],
                'menu'
            );

            $previewItem->addChild(
                $this->getContentPreviewItem(
                    $location,
                    $content,
                    $language,
                    [
                        'label' => $subPreviewItemLabel,
                    ],
                    '__' . $languageCode
                )
            );
        }

        $menu->addChild($previewItem);

        $canSendInvitation = $this->permissionResolver->canUser(
            'user',
            'invite',
            $content
        );

        if ($contentIsUserGroup && $canSendInvitation) {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__INVITE,
                    [
                        'extras' => ['orderNumber' => 15],
                        'attributes' => [
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#ibexa-user-invitation-modal',
                        ],
                    ]
                )
            );
        }

        $this->addEditMenuItem($menu, $contentIsUser, $canEdit);

        $menu->addChild(
            $this->createMenuItem(
                self::ITEM__MOVE,
                [
                    'extras' => ['orderNumber' => 30],
                    'attributes' => $hasCreatePermission
                        ? $moveAttributes
                        : array_merge($moveAttributes, ['disabled' => 'disabled']),
                ]
            )
        );
        if (!$contentIsUser && !$contentIsUserGroup) {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__COPY,
                    [
                        'extras' => ['orderNumber' => 40],
                        'attributes' => $canCopy
                            ? $copyAttributes
                            : array_merge($copyAttributes, ['disabled' => 'disabled']),
                    ]
                )
            );

            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__COPY_SUBTREE,
                    [
                        'extras' => ['orderNumber' => 50],
                        'attributes' => $canCopySubtree
                            ? $copySubtreeAttributes
                            : array_merge($copySubtreeAttributes, ['disabled' => 'disabled']),
                    ]
                )
            );
        }

        if ($content->getVersionInfo()->getContentInfo()->isHidden) {
            $this->addRevealMenuItem($menu, $canHide);
        } else {
            $this->addHideMenuItem($menu, $canHide);
        }

        if ($contentIsUser && $canDelete) {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__DELETE,
                    [
                        'extras' => ['orderNumber' => 70],
                        'attributes' => [
                            'data-bs-toggle' => 'modal',
                            'data-bs-target' => '#delete-user-modal',
                        ],
                    ]
                )
            );
        }

        if (!$contentIsUser && 1 !== $location->depth && $canTrashLocation) {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__SEND_TO_TRASH,
                    [
                        'extras' => ['orderNumber' => 80],
                        'attributes' => $sendToTrashAttributes,
                    ]
                )
            );
        }

        if (1 === $location->depth) {
            $menu[self::ITEM__MOVE]->setAttribute('disabled', 'disabled');
        }

        return $menu;
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__CREATE, 'ibexa_menu'))->setDesc('Create content'),
            (new Message(self::ITEM__EDIT, 'ibexa_menu'))->setDesc('Edit'),
            (new Message(self::ITEM__SEND_TO_TRASH, 'ibexa_menu'))->setDesc('Send to trash'),
            (new Message(self::ITEM__COPY, 'ibexa_menu'))->setDesc('Copy'),
            (new Message(self::ITEM__COPY_SUBTREE, 'ibexa_menu'))->setDesc('Copy Subtree'),
            (new Message(self::ITEM__MOVE, 'ibexa_menu'))->setDesc('Move'),
            (new Message(self::ITEM__DELETE, 'ibexa_menu'))->setDesc('Delete'),
            (new Message(self::ITEM__HIDE, 'ibexa_menu'))->setDesc('Hide'),
            (new Message(self::ITEM__REVEAL, 'ibexa_menu'))->setDesc('Reveal'),
            (new Message(self::ITEM__INVITE, 'ibexa_menu'))->setDesc('Invite members'),
        ];
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     * @param bool $contentIsUser
     * @param bool $canEdit
     */
    private function addEditMenuItem(ItemInterface $menu, bool $contentIsUser, bool $canEdit): void
    {
        $editAttributes = [
            'class' => 'ibexa-btn--extra-actions ibexa-btn--edit',
            'data-actions' => 'edit',
        ];
        $editUserAttributes = [
            'class' => 'ibexa-btn--extra-actions ibexa-btn--edit-user',
            'data-actions' => 'edit-user',
        ];

        if ($contentIsUser) {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__EDIT,
                    [
                        'extras' => ['orderNumber' => 20],
                        'attributes' => $canEdit
                            ? $editUserAttributes
                            : array_merge($editUserAttributes, ['disabled' => 'disabled']),
                    ]
                )
            );
        } else {
            $menu->addChild(
                $this->createMenuItem(
                    self::ITEM__EDIT,
                    [
                        'extras' => ['orderNumber' => 20],
                        'attributes' => $canEdit
                            ? $editAttributes
                            : array_merge($editAttributes, ['disabled' => 'disabled']),
                    ]
                )
            );
        }
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     */
    private function addRevealMenuItem(ItemInterface $menu, bool $canHide): void
    {
        $attributes = [
            'class' => 'ibexa-btn--reveal',
            'data-actions' => 'reveal',
        ];

        $menu->addChild(
            $this->createMenuItem(
                self::ITEM__REVEAL,
                [
                    'extras' => ['orderNumber' => 60],
                    'attributes' => $canHide
                        ? $attributes
                        : array_merge($attributes, ['disabled' => 'disabled']),
                ]
            )
        );
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     */
    private function addHideMenuItem(ItemInterface $menu, bool $canHide): void
    {
        $attributes = [
            'class' => 'ibexa-btn--hide',
            'data-actions' => 'hide',
        ];

        $menu->addChild(
            $this->createMenuItem(
                self::ITEM__HIDE,
                [
                    'extras' => ['orderNumber' => 60],
                    'attributes' => $canHide
                        ? $attributes
                        : array_merge($attributes, ['disabled' => 'disabled']),
                ]
            )
        );
    }

    private function hasCreatePermission(): bool
    {
        $createPolicies = $this->permissionResolver->hasAccess(
            'content',
            'create'
        );

        return !is_bool($createPolicies) || $createPolicies;
    }

    private function canCopy(bool $hasCreatePermission): bool
    {
        $manageLocationsPolicies = $this->permissionResolver->hasAccess(
            'content',
            'manage_locations'
        );

        $hasManageLocationsPermission = !is_bool($manageLocationsPolicies) || $manageLocationsPolicies;

        return $hasCreatePermission && $hasManageLocationsPermission;
    }

    private function canCopySubtree(Location $location, bool $hasCreatePermission): bool
    {
        if (!$hasCreatePermission) {
            return false;
        }

        $isWithinCopySubtreeLimit = new IsWithinCopySubtreeLimit(
            $this->getCopySubtreeLimit(),
            $this->locationService
        );

        return (new IsRoot())->not()->and($isWithinCopySubtreeLimit)->isSatisfiedBy($location);
    }

    private function getCopySubtreeLimit(): int
    {
        return $this->configResolver->getParameter(
            'subtree_operations.copy_subtree.limit'
        );
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $parentLocation
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function getContentPreviewItem(
        ?Location $location,
        Content $content,
        Language $language,
        array $options,
        string $idPostfix = ''
    ): ItemInterface {
        $versionNo = $content->getVersionInfo()->versionNo;

        $siteAccesses = $this->siteaccessResolver->getSiteAccessesListForLocation(
            $location,
            $versionNo,
            $language->languageCode
        );

        $canPreview = $this->permissionResolver->canUser(
            'content',
            'versionread',
            $content,
            [$location]
        );

        if ($canPreview && !empty($siteAccesses)) {
            $actionOptions = [
                'route' => 'ibexa.content.preview',
                'routeParameters' => [
                    'contentId' => $content->contentInfo->getId(),
                    'versionNo' => $content->getVersionInfo()->versionNo,
                    'languageCode' => $language->languageCode,
                    'locationId' => $location->id,
                ],
            ];
        } else {
            $actionOptions = [
                'attributes' => ['disabled' => 'disabled'],
            ];
        }

        return $this->createMenuItem(
            self::ITEM__PREVIEW . $idPostfix,
            array_merge($options, $actionOptions)
        );
    }
}

class_alias(ContentRightSidebarBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\ContentRightSidebarBuilder');
