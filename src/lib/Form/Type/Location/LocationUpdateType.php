<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Location;

use Ibexa\AdminUi\Form\Data\Location\LocationUpdateData;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\AdminUi\Form\Type\ContentType\SortFieldChoiceType;
use Ibexa\AdminUi\Form\Type\ContentType\SortOrderChoiceType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'location',
                LocationType::class,
                ['label' => false]
            )
            ->add(
                'sort_field',
                SortFieldChoiceType::class,
                ['label' => /** @Desc("Sort field") */ 'location_update_form.sort_field']
            )
            ->add(
                'sort_order',
                SortOrderChoiceType::class,
                ['label' => /** @Desc("Sort order") */ 'location_update_form.sort_order']
            )
            ->add(
                'update',
                SubmitType::class,
                ['label' => /** @Desc("Update") */ 'location_update_form.update']
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LocationUpdateData::class,
            'translation_domain' => 'ibexa_content_type',
        ]);
    }
}
