<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Menu;

use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractActionBuilder extends AbstractBuilder
{
    protected const TRANSLATION_DOMAIN = 'ibexa_action_menu';
    protected const IBEXA_BTN_CONTENT_DRAFT_EDIT_CLASS = 'ibexa-btn--content-draft-edit';

    private const ICON_EDIT = 'edit';
    private const ORDER_NUMBER = 200;

    protected TranslatorInterface $translator;

    protected UrlGeneratorInterface $urlGenerator;

    private ContentService $contentService;

    private UserService $userService;

    public function __construct(
        MenuItemFactoryInterface $menuItemFactory,
        EventDispatcherInterface $eventDispatcher,
        ContentService $contentService,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        UserService $userService
    ) {
        parent::__construct($menuItemFactory, $eventDispatcher);

        $this->contentService = $contentService;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->userService = $userService;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function createActionItem(string $name, array $options = []): ItemInterface
    {
        if (empty($options['extras']['translation_domain'])) {
            $options['extras']['translation_domain'] = self::TRANSLATION_DOMAIN;
        }

        return $this->createMenuItem($name, $options);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function createEditDraftButtonAction(
        VersionInfo $versionInfo,
        string $name,
        array $parameters = [],
        ?int $locationId = null
    ): ItemInterface {
        $parameters['attributes']['data-content-id'] = $versionInfo->getContentInfo()->getId();
        $parameters['attributes']['data-language-code'] = $versionInfo->getInitialLanguage()->getLanguageCode();
        $parameters['attributes']['data-version-has-conflict-url'] = $this->generateVersionHasConflictUrl($versionInfo);
        $parameters['attributes']['data-content-draft-edit-url'] = $this->generateDraftEditUrl($versionInfo, $locationId);
        $parameters['extras']['icon'] = $parameters['extras']['icon'] ?? self::ICON_EDIT;
        $parameters['extras']['orderNumber'] = $parameters['extras']['orderNumber'] ?? self::ORDER_NUMBER;

        return $this->createActionItem($name, $parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function createDraftEditLinkAction(
        VersionInfo $versionInfo,
        string $name,
        array $parameters = [],
        ?int $locationId = null
    ): ItemInterface {
        $parameters['uri'] = $this->generateDraftEditUrl($versionInfo, $locationId);
        $parameters['extras']['icon'] = $parameters['extras']['icon'] ?? self::ICON_EDIT;
        $parameters['extras']['orderNumber'] = $parameters['extras']['orderNumber'] ?? self::ORDER_NUMBER;

        return $this->createActionItem($name, $parameters);
    }

    private function generateVersionHasConflictUrl(VersionInfo $versionInfo): string
    {
        return $this->urlGenerator->generate(
            'ibexa.version.has_no_conflict',
            [
                'contentId' => $versionInfo->getContentInfo()->getId(),
                'versionNo' => $versionInfo->getVersionNo(),
                'languageCode' => $versionInfo->getInitialLanguage()->getLanguageCode(),
            ]
        );
    }

    private function generateDraftEditUrl(
        VersionInfo $versionInfo,
        ?int $locationId = null
    ): string {
        $routeName = 'ibexa.content.draft.edit';

        if ($this->isUserBased($versionInfo)) {
            $routeName = 'ibexa.user.update';
        }

        return $this->urlGenerator->generate(
            $routeName,
            [
                'contentId' => $versionInfo->getContentInfo()->getId(),
                'versionNo' => $versionInfo->getVersionNo(),
                'language' => $versionInfo->getInitialLanguage()->getLanguageCode(),
                'locationId' => $locationId,
            ]
        );
    }

    private function isUserBased(VersionInfo $versionInfo): bool
    {
        $content = $this->contentService->loadContentByVersionInfo($versionInfo);

        return (new ContentIsUser($this->userService))->isSatisfiedBy($content);
    }
}
