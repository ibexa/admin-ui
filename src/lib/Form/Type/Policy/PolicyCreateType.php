<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Policy;

use Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData>
 */
final class PolicyCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'policy',
                PolicyChoiceType::class,
                [
                    'label' => /** @Desc("Policy") */ 'role.policy.type',
                    'placeholder' => /** @Desc("Choose a Policy") */ 'role.policy.type.choose',
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => /** @Desc("Save") */ 'policy_create.save']
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PolicyCreateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
