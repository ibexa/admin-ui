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
    public const EXPERT = '0';
    public const SMART = '1';
    private const TRANSLATION_DOMAIN = 'ibexa_user_settings';

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
            self::TRANSLATION_DOMAIN
        );
    }

    public function getDescription(): string
    {
        return $this->translator->trans(
            'user.setting.mode.description',
            [],
            self::TRANSLATION_DOMAIN
        );
    }

    public function getDisplayValue(string $storageValue): string
    {
        switch (true) {
            case $storageValue === self::EXPERT:
                return $this->translator->trans('user.setting.mode.expert', [], self::TRANSLATION_DOMAIN);
            case $storageValue === self::SMART:
                return $this->translator->trans('user.setting.mode.smart', [], self::TRANSLATION_DOMAIN);
        }

        return $storageValue;
    }

    public function getDefaultValue(): string
    {
        return $this->configResolver->getParameter('default.user_mode');
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
                'translation_domain' => self::TRANSLATION_DOMAIN,
                'help' => $this->translator->trans('user.setting.mode.help', [], self::TRANSLATION_DOMAIN),
                'help_html' => true,
            ]
        );
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message('user.setting.mode.help', self::TRANSLATION_DOMAIN))
                ->setDesc(
                    '<p><strong>Smart mode</strong> – A clean and intuitive interface with a simplified content structure, designed for new and non-advanced users. Features include:</p>
                        <ul>
                            <li>Quick preview</li>
                            <li>Hidden Technical Details tab</li>
                            <li>Hidden Locations and Versions tabs in Content items</li>
                        </ul>
                        <p><strong>Expert mode</strong> – Tailored for experienced users familiar with Ibexa DXP. Provides comprehensive insights into the technical aspects of Content structure, including:</p>
                        <ul>
                            <li>Technical Details tab</li>
                            <li>Location: Archived versions</li>
                        </ul>'
                ),
            (new Message('user.setting.mode.expert', self::TRANSLATION_DOMAIN))->setDesc('Expert'),
            (new Message('user.setting.mode.smart', self::TRANSLATION_DOMAIN))->setDesc('Smart'),
            (new Message('user.setting.mode.name', self::TRANSLATION_DOMAIN))->setDesc('Mode'),
            (new Message('user.setting.mode.description', self::TRANSLATION_DOMAIN))->setDesc('Mode'),
        ];
    }
}
