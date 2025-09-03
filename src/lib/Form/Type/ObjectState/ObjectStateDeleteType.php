<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ObjectState;

use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateDeleteData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateDeleteData>
 */
final class ObjectStateDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('object_state_', ObjectStateType::class)
            ->add('delete', SubmitType::class, [
                'label' => /** @Desc("Delete") */
                    'object_state_.delete.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ObjectStateDeleteData::class,
            'translation_domain' => 'ibexa_object_state',
        ]);
    }
}
