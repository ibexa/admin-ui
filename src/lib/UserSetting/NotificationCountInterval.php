<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UserSetting;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\User\UserSetting\FormMapperInterface;
use Ibexa\Contracts\User\UserSetting\ValueDefinitionInterface;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * How often the back office checks for new notifications, in milliseconds, like the `notification_count.interval`
 * SiteAccess setting that gives its default.
 */
final readonly class NotificationCountInterval implements ValueDefinitionInterface, FormMapperInterface, TranslationContainerInterface
{
    public const string IDENTIFIER = 'notification_count_interval';

    private const int MS_PER_SECOND = 1000;
    private const int MS_PER_MINUTE = 60000;
    /** The choices of the form; the shipped default (30 seconds) is one of them. */
    private const array PRESETS = ['30000', '60000', '300000', '900000', '1800000'];

    public function __construct(
        private TranslatorInterface $translator,
        private ConfigResolverInterface $configResolver
    ) {}

    public function getName(): string
    {
        return $this->translator->trans(
            /** @Desc("Notification check frequency") */
            'settings.notification_count_interval.value.title',
            [],
            'ibexa_user_settings'
        );
    }

    public function getDescription(): string
    {
        return $this->translator->trans(
            /** @Desc("Check for new notifications every") */
            'settings.notification_count_interval.value.description',
            [],
            'ibexa_user_settings'
        );
    }

    public function getDisplayValue(string $storageValue): string
    {
        return $this->createLabel($this->resolveInterval($storageValue))->trans($this->translator);
    }

    public function getDefaultValue(): string
    {
        return (string)$this->configResolver->getParameter('notification_count.interval');
    }

    /**
     * The interval to use for a stored value. A value that is not one of the presets (anymore) falls back to the
     * SiteAccess default, not to the JS one.
     */
    public function resolveInterval(string $storageValue): int
    {
        return in_array($storageValue, self::PRESETS, true)
            ? (int)$storageValue
            : (int)$this->getDefaultValue();
    }

    /**
     * @param FormBuilderInterface<mixed> $formBuilder
     *
     * @return FormBuilderInterface<mixed>
     */
    public function mapFieldForm(
        FormBuilderInterface $formBuilder,
        ValueDefinitionInterface $value
    ): FormBuilderInterface {
        return $formBuilder->create(
            'value',
            ChoiceType::class,
            [
                'multiple' => false,
                'required' => true,
                'label' => $this->getDescription(),
                'choices' => array_combine(self::PRESETS, self::PRESETS),
                // Translated once, when the form is rendered.
                'choice_label' => fn (string $choice): TranslatableMessage => $this->createLabel((int)$choice),
                'constraints' => [new Choice(choices: self::PRESETS)],
            ]
        );
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message('settings.notification_count_interval.value.minutes', 'ibexa_user_settings'))
                ->setDesc('{1}%count% minute|]1,Inf]%count% minutes'),
            (new Message('settings.notification_count_interval.value.seconds', 'ibexa_user_settings'))
                ->setDesc('{1}%count% second|]1,Inf]%count% seconds'),
        ];
    }

    private function createLabel(int $interval): TranslatableMessage
    {
        if ($interval % self::MS_PER_MINUTE === 0) {
            return new TranslatableMessage(
                'settings.notification_count_interval.value.minutes',
                ['%count%' => intdiv($interval, self::MS_PER_MINUTE)],
                'ibexa_user_settings'
            );
        }

        return new TranslatableMessage(
            'settings.notification_count_interval.value.seconds',
            ['%count%' => intdiv($interval, self::MS_PER_SECOND)],
            'ibexa_user_settings'
        );
    }
}
