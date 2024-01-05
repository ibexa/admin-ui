<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UserSetting;

use Ibexa\AdminUi\Form\Type\User\UserModeChoiceType;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\User\UserSetting\FormMapperInterface;
use Ibexa\Contracts\User\UserSetting\ValueDefinitionInterface;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FocusMode implements ValueDefinitionInterface, FormMapperInterface, TranslationContainerInterface
{
    public const IDENTIFIER = 'focus_mode';

    public const FOCUS_MODE_OFF = '0';
    public const FOCUS_MODE_ON = '1';

    private TranslatorInterface $translator;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        TranslatorInterface $translator
    ) {
        $this->configResolver = $configResolver;
        $this->translator = $translator;
    }

    public function getName(): string
    {
        return $this->translator->trans(
            /** @Desc("Focus mode") */
            'user.setting.focus_mode.name',
            [],
            'ibexa_user_settings'
        );
    }

    public function getDescription(): string
    {
        return $this->translator->trans(
            /** @Desc("Focus mode") */
            'user.setting.focus_mode.description',
            [],
            'ibexa_user_settings'
        );
    }

    public function getDisplayValue(string $storageValue): string
    {
        $translationMap = [
            self::FOCUS_MODE_OFF => $this->translator->trans(/** @Desc("Off") */'user.setting.focus_mode.off', [], 'ibexa_user_settings'),
            self::FOCUS_MODE_ON => $this->translator->trans(/** @Desc("On") */'user.setting.focus_mode.on', [], 'ibexa_user_settings'),
        ];

        return $translationMap[$storageValue] ?? $storageValue;
    }

    public function getDefaultValue(): string
    {
        return $this->configResolver->getParameter('admin_ui.default_focus_mode');
    }

    public function mapFieldForm(
        FormBuilderInterface $formBuilder,
        ValueDefinitionInterface $value
    ): FormBuilderInterface {
        return $formBuilder->create(
            'value',
            UserModeChoiceType::class,
            [
                'label' => 'user.setting.mode.name',
                'expanded' => true,
                'multiple' => false,
                'translation_domain' => 'ibexa_user_settings',
                'help' => $this->translator->trans('user.setting.mode.help', [], 'ibexa_user_settings'),
                'help_html' => true,
            ]
        );
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message('user.setting.focus_mode.help', 'ibexa_user_settings'))
                ->setDesc(
                    '<p><strong>Focus mode: on</strong> – A clean and intuitive interface with a simplified content
                        structure, designed for new and non-advanced users. Features include:</p>
                        <ul>
                            <li>View</li>
                            <li>Hidden Technical details tab</li>
                            <li>Hidden Locations and Versions tabs in Content items</li>
                        </ul>
                        <p><strong>Focus mode: off</strong> – Tailored for experienced users familiar with Ibexa DXP.
                        Provides comprehensive insights into the technical aspects of Content structure, including:</p>
                        <ul>
                            <li>Technical details tab</li>
                            <li>Location: Archived versions</li>
                        </ul>'
                ),
        ];
    }
}
