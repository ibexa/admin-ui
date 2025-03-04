<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Version;

use Ibexa\AdminUi\Form\Data\Version\VersionRemoveData;
use Ibexa\AdminUi\Form\Type\Content\ContentInfoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VersionRemoveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content_info', ContentInfoType::class)
            ->add('versions', CollectionType::class, [
                'entry_type' => CheckboxType::class,
                'required' => false,
                'allow_add' => true,
                'entry_options' => ['label' => false],
            ])
            ->add('remove', SubmitType::class, [
                'attr' => ['hidden' => true],
                'label' => /** @Desc("Remove version") */ 'version_remove_form.remove',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VersionRemoveData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
