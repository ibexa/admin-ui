<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\User;

use Ibexa\AdminUi\UserSetting\FocusMode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserModeChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_user_settings',
            'choices' => [
                'user.setting.focus_mode.on' => FocusMode::FOCUS_MODE_ON,
                'user.setting.focus_mode.off' => FocusMode::FOCUS_MODE_OFF,
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
