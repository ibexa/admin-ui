<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentTypeGroup;

use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupUpdateData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeGroupUpdateType extends AbstractType
{
    public const BTN_SAVE = 'save';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identifier', TextType::class, [
                'label' => /** @Desc("Name") */ 'content_type_group.update.name',
            ])
            ->add('update', SubmitType::class, [
                'label' => /** @Desc("Save and close") */ 'content_type_group.update.submit',
            ])
            ->add(self::BTN_SAVE, SubmitType::class, [
                'label' => /** @Desc("Save") */ 'content_type_group.update.save',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentTypeGroupUpdateData::class,
            'translation_domain' => 'ibexa_content_type',
        ]);
    }
}

class_alias(ContentTypeGroupUpdateType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupUpdateType');
