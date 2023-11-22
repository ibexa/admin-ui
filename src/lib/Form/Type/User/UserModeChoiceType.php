<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\User;

use Ibexa\AdminUi\UserSetting\UserMode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserModeChoiceType extends AbstractType
{
    private const TRANSLATION_DOMAIN = 'ibexa_user_settings';

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => self::TRANSLATION_DOMAIN,
            'choices' => [
                'user.setting.mode.expert' => UserMode::EXPERT,
                'user.setting.mode.smart' => UserMode::SMART,
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
