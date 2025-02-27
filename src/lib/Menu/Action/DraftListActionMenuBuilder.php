<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Menu\Action;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DraftListActionMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    public const ITEM_EDIT_DRAFT = 'draft_list__action__content_edit';

    private ContentService $contentService;

    private TranslatorInterface $translator;

    private UrlGeneratorInterface $urlGenerator;

    private UserService $userService;

    public function __construct(
        MenuItemFactoryInterface $factory,
        EventDispatcherInterface $eventDispatcher,
        ContentService $contentService,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        UserService $userService
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->contentService = $contentService;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->userService = $userService;
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_DRAFT_LIST_ACTION;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function createStructure(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root_action_list');

        $versionInfo = $options['versionInfo'];
        if (!$versionInfo instanceof VersionInfo) {
            throw new InvalidArgumentException(
                '$versionInfo',
                'Version info expected to be type of ' . VersionInfo::class
            );
        }

        $content = $this->contentService->loadContentByVersionInfo($versionInfo);
        $contentEditDraftAction = $this->createEditDraftAction($content);

        $menu->addChild($contentEditDraftAction);

        return $menu;
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM_EDIT_DRAFT, 'ibexa_action_menu'))->setDesc('Edit'),
        ];
    }

    private function createEditDraftAction(Content $content): ItemInterface
    {
        $versionInfo = $content->getVersionInfo();

        return $this->createMenuItem(
            self::ITEM_EDIT_DRAFT,
            [
                'label' => $this->translator->trans(
                    self::ITEM_EDIT_DRAFT,
                    [],
                    'ibexa_action_menu'
                ),
                'attributes' => [
                    'data-content-id' => $versionInfo->getContentInfo()->getId(),
                    'data-language-code' => $versionInfo->getInitialLanguage()->getLanguageCode(),
                    'data-version-has-conflict-url' => $this->generateVersionHasConflictUrl($versionInfo),
                    'data-content-draft-edit-url' => $this->generateDraftEditUrl($content),
                ],
                'extras' => [
                    'icon' => 'edit',
                    'orderNumber' => 100,
                ],
            ]
        );
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

    private function generateDraftEditUrl(Content $content): string
    {
        $routeName = 'ibexa.content.draft.edit';

        $contentIsUser = (new ContentIsUser($this->userService))->isSatisfiedBy($content);
        if ($contentIsUser) {
            $routeName = 'ibexa.user.update';
        }

        $versionInfo = $content->getVersionInfo();

        return $this->urlGenerator->generate(
            $routeName,
            [
                'contentId' => $versionInfo->getContentInfo()->getId(),
                'versionNo' => $versionInfo->getVersionNo(),
                'language' => $versionInfo->getInitialLanguage()->getLanguageCode(),
            ]
        );
    }
}
