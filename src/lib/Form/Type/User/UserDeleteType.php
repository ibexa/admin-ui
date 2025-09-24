<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\User;

use Ibexa\AdminUi\Form\Data\User\UserDeleteData;
use Ibexa\AdminUi\Form\Type\Content\ContentInfoType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\User\UserDeleteData>
 */
class UserDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'content_info',
                ContentInfoType::class,
                ['label' => false]
            )
            ->add(
                'delete',
                SubmitType::class,
                ['label' => /** @Desc("Delete") */ 'user_delete_form.delete']
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserDeleteData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
