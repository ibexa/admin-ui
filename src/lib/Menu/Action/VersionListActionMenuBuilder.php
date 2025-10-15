<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Action;

use Ibexa\Contracts\AdminUi\Menu\AbstractActionBuilder;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;

final class VersionListActionMenuBuilder extends AbstractActionBuilder implements TranslationContainerInterface
{
    public const string ITEM_EDIT_DRAFT = 'version_list__action__content_edit';
    public const string ITEM_RESTORE_VERSION = 'version_list__action__restore_version';

    private const string ICON_ARCHIVE_RESTORE = 'archive-restore';

    protected function getConfigureEventName(): string
    {
        return self::class;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    protected function createStructure(array $options): ItemInterface
    {
        $versionInfo = $options['versionInfo'];
        if (!$versionInfo instanceof VersionInfo) {
            throw new InvalidArgumentException(
                '$versionInfo',
                sprintf(
                    'Version info expected to be of type "%s" but got "%s"',
                    VersionInfo::class,
                    get_debug_type($versionInfo)
                )
            );
        }

        $menu = $this->createActionItem('root_action_list');

        if ($versionInfo->isDraft()) {
            $isDraftConflict = $options['isDraftConflict'] ?? false;
            $locationId = $options['locationId'] ?? null;

            $parameters['label'] = $this->translator->trans(
                /** @Ignore */
                self::ITEM_EDIT_DRAFT,
                [],
                self::TRANSLATION_DOMAIN
            );

            $editDraftActionItem = $this->createEditDraftAction(
                $versionInfo,
                $locationId,
                $parameters,
                $isDraftConflict
            );

            $menu->addChild($editDraftActionItem);
        }

        if ($versionInfo->isArchived()) {
            $restoreVersionActionItem = $this->createArchiveRestoreAction($versionInfo);
            $menu->addChild($restoreVersionActionItem);
        }

        return $menu;
    }

    /**
     * @return array<\JMS\TranslationBundle\Model\Message>
     */
    public static function getTranslationMessages(): array
    {
        return [
            Message::create(self::ITEM_EDIT_DRAFT, 'ibexa_action_menu')->setDesc('Edit'),
            Message::create(self::ITEM_RESTORE_VERSION, 'ibexa_action_menu')->setDesc('Restore archived version'),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function createEditDraftAction(
        VersionInfo $versionInfo,
        ?int $locationId,
        array $parameters,
        bool $isDraftConflict = false
    ): ItemInterface {
        $parameters['attributes']['class'] = self::IBEXA_BTN_CONTENT_DRAFT_EDIT_CLASS;
        $parameters['attributes']['data-tooltip-container-selector'] = '.ibexa-modal';

        if ($isDraftConflict) {
            return $this->createDraftEditLinkAction($versionInfo, self::ITEM_EDIT_DRAFT, $parameters, $locationId);
        }

        return $this->createEditDraftButtonAction($versionInfo, self::ITEM_EDIT_DRAFT, $parameters, $locationId);
    }

    private function createArchiveRestoreAction(VersionInfo $versionInfo): ItemInterface
    {
        return $this->createActionItem(
            self::ITEM_RESTORE_VERSION,
            [
                'label' => $this->translator->trans(
                    /** @Ignore */
                    self::ITEM_RESTORE_VERSION,
                    [],
                    self::TRANSLATION_DOMAIN
                ),
                'attributes' => [
                    'class' => 'ibexa-btn--content-edit',
                    'data-content-id' => $versionInfo->getContentInfo()->getId(),
                    'data-language-code' => $versionInfo->getInitialLanguage()->getLanguageCode(),
                    'data-version-no' => $versionInfo->getVersionNo(),
                ],
                'extras' => [
                    'icon' => self::ICON_ARCHIVE_RESTORE,
                    'orderNumber' => 10,
                ],
            ]
        );
    }
}
