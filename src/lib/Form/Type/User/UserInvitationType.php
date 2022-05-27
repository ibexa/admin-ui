<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\User;

use Ibexa\AdminUi\Form\Type\Content\ContentInfoType;
use Ibexa\CorporateAccount\Form\Data\Invitation\SimpleInvitationData;
use Ibexa\CorporateAccount\Form\Type\Invitation\SimpleInvitationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

final class UserInvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(
                'emails',
                CollectionType::class,
                [
                    'entry_type' => EmailType::class,
                    'allow_add' => true,
                    'data' => [''],
                    'label' => false,
                    'entry_options' => [
                        'label' => false,
                    ],
                ]
            )
            ->add(
                'send',
                SubmitType::class,
            )
        ;
    }
}
