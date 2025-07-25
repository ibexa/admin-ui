<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\Date;

use Ibexa\AdminUi\Form\DataTransformer\DateIntervalTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType as BaseDateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class DateIntervalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_interval', BaseDateIntervalType::class, [
                'attr' => ['hidden' => true],
                'input' => 'string',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('start_date', IntegerType::class, [
                'attr' => ['hidden' => true],
                'required' => false,
            ])
            ->add('end_date', IntegerType::class, [
                'attr' => ['hidden' => true],
                'required' => false,
            ])
            ->addModelTransformer(new DateIntervalTransformer());
    }
}
