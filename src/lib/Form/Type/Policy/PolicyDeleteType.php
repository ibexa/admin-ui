<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Policy;

use Ibexa\AdminUi\Form\Data\Policy\PolicyDeleteData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolicyDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'policy',
                PolicyType::class,
                ['label' => false]
            )
            ->add(
                'delete',
                SubmitType::class,
                ['label' => /** @Desc("Delete") */ 'policy_delete.delete']
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_content_forms_role',
            'data_class' => PolicyDeleteData::class,
        ]);
    }
}
