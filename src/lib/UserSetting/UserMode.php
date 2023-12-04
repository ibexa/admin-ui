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
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserMode implements ValueDefinitionInterface, FormMapperInterface, TranslationContainerInterface
{
    public const IDENTIFIER = 'user_mode';

    public const EXPERT = '0';
    public const SMART = '1';

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
            'user.setting.mode.name',
            [],
            'ibexa_user_settings'
        );
    }

    public function getDescription(): string
    {
        return $this->translator->trans(
            'user.setting.mode.description',
            [],
            'ibexa_user_settings'
        );
    }

    public function getDisplayValue(string $storageValue): string
    {
        $translationMap = [
            self::EXPERT => $this->translator->trans('user.setting.mode.expert', [], 'ibexa_user_settings'),
            self::SMART => $this->translator->trans('user.setting.mode.smart', [], 'ibexa_user_settings'),
        ];

        return $translationMap[$storageValue] ?? $storageValue;
    }

    public function getDefaultValue(): string
    {
        return $this->configResolver->getParameter('admin_ui.default_user_mode');
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
            (new Message('user.setting.mode.help', 'ibexa_user_settings'))
                ->setDesc(
                    '<p><strong>Smart mode</strong> – A clean and intuitive interface with a simplified content
                        structure, designed for new and non-advanced users. Features include:</p>
                        <ul>
                            <li>Quick preview</li>
                            <li>Hidden Technical Details tab</li>
                            <li>Hidden Locations and Versions tabs in Content items</li>
                        </ul>
                        <p><strong>Expert mode</strong> – Tailored for experienced users familiar with Ibexa DXP.
                        Provides comprehensive insights into the technical aspects of Content structure, including:</p>
                        <ul>
                            <li>Technical Details tab</li>
                            <li>Location: Archived versions</li>
                        </ul>'
                ),
            (new Message('user.setting.mode.expert', 'ibexa_user_settings'))->setDesc('Expert'),
            (new Message('user.setting.mode.smart', 'ibexa_user_settings'))->setDesc('Smart'),
            (new Message('user.setting.mode.name', 'ibexa_user_settings'))->setDesc('Mode'),
            (new Message('user.setting.mode.description', 'ibexa_user_settings'))->setDesc('Mode'),
        ];
    }
}
