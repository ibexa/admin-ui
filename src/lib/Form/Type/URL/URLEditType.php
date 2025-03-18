<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\URL;

use Ibexa\AdminUi\Form\Data\URL\URLUpdateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class URLEditType extends AbstractType
{
    public const BTN_SAVE = 'save';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('url', TextType::class)
            ->add(self::BTN_SAVE, SubmitType::class, [
                'label' => /** @Desc("Save") */ 'url.save',
            ])
            ->add('save_and_close', SubmitType::class, [
                'label' => /** @Desc("Save and close") */ 'url.save_and_close',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => URLUpdateData::class,
            'translation_domain' => 'ibexa_content_forms_url',
        ]);
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_url_edit';
    }
}
