<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content\Location;

use Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData;
use Ibexa\AdminUi\Form\Type\Content\ContentInfoType;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentMainLocationUpdateType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    protected $locationService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content_info',
                ContentInfoType::class,
                ['label' => false]
            )
            ->add(
                'location',
                LocationType::class,
                [
                    'required' => false,
                    'label' => false,
                ]
            )
            ->add(
                'update',
                SubmitType::class,
                [
                    'attr' => ['hidden' => true],
                    'label' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContentMainLocationUpdateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
