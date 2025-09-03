<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content;

use Ibexa\AdminUi\Form\DataTransformer\LocationsTransformer;
use Ibexa\AdminUi\Form\DataTransformer\LocationTransformer;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
class LocationType extends AbstractType
{
    public function __construct(protected LocationService $locationService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(
            $options['multiple']
            ? new LocationsTransformer($this->locationService)
            : new LocationTransformer($this->locationService)
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('multiple', false);
        $resolver->setRequired(['multiple']);
        $resolver->setAllowedTypes('multiple', 'boolean');
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
