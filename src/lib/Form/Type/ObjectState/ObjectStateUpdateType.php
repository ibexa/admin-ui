<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ObjectState;

use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateUpdateData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectStateUpdateType extends AbstractType
{
    public const BTN_SAVE = 'save';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identifier', TextType::class, [
                'label' => /** @Desc("Identifier") */ 'object_state.update.identifier',
            ])
            ->add('name', TextType::class, [
                'label' => /** @Desc("Name") */ 'object_state.update.name',
            ])
            ->add(self::BTN_SAVE, SubmitType::class, [
                'label' => /** @Desc("Save") */ 'object_state.update.save',
            ])
            ->add('save_and_close', SubmitType::class, [
                'label' => /** @Desc("Save and close") */ 'object_state.update.save_and_close',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ObjectStateUpdateData::class,
            'translation_domain' => 'ibexa_object_state',
        ]);
    }
}

class_alias(ObjectStateUpdateType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\ObjectState\ObjectStateUpdateType');
