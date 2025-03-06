<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Menu\Action;

use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\Contracts\AdminUi\Menu\ContentAwareActionItemFactoryInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContentAwareActionItemFactory implements ContentAwareActionItemFactoryInterface
{
    private const ICON_EDIT = 'edit';

    private ContentService $contentService;

    private TranslatorInterface $translator;

    private UrlGeneratorInterface $urlGenerator;

    private UserService $userService;

    private FactoryInterface $menuFactory;

    public function __construct(
        ContentService $contentService,
        FactoryInterface $menuFactory,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        UserService $userService
    ) {
        $this->contentService = $contentService;
        $this->menuFactory = $menuFactory;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->userService = $userService;
    }

    public function createEditDraftAction(
        string $name,
        VersionInfo $versionInfo,
        bool $isDraftConflict = false,
        ?int $locationId = null,
        ?int $orderNumber = 0
    ): ItemInterface {
        return $this->menuFactory->createItem(
            $name,
            [
                'uri' => $isDraftConflict ? $this->generateDraftEditUrl($versionInfo, $locationId) : null,
                'label' => $this->translator->trans(
                    $name,
                    [],
                    'ibexa_action_menu'
                ),
                'attributes' => [
                    'class' => 'ibexa-btn--content-draft-edit',
                    'data-content-id' => $versionInfo->getContentInfo()->getId(),
                    'data-language-code' => $versionInfo->getInitialLanguage()->getLanguageCode(),
                    'data-version-has-conflict-url' => $this->generateVersionHasConflictUrl($versionInfo),
                    'data-content-draft-edit-url' => $this->generateDraftEditUrl($versionInfo),
                ],
                'extras' => [
                    'icon' => self::ICON_EDIT,
                    'orderNumber' => $orderNumber,
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
