<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\URLWildcard;

use Ibexa\AdminUi\Form\Data\URLWildcard\URLWildcardUpdateData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class URLWildcardUpdateType extends AbstractType
{
    public const BTN_SAVE = 'save';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('destination_url', TextType::class, [
                'label' => /** @Desc("Destination URL") */ 'url_wildcard.create.identifier',
            ])
            ->add('source_url', TextType::class, [
                'label' => /** @Desc("Source URL") */ 'url_wildcard.create.source_url',
            ])
            ->add('forward', CheckboxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add(self::BTN_SAVE, SubmitType::class, [
                'label' => /** @Desc("Save") */ 'url_wildcard.save',
            ])
            ->add('save_and_close', SubmitType::class, [
                'label' => /** @Desc("Save and close") */ 'url_wildcard.save_and_close',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => URLWildcardUpdateData::class,
            'translation_domain' => 'ibexa_url_wildcard',
        ]);
    }
}
