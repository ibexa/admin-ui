<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Location;

use Ibexa\AdminUi\Form\Data\Location\LocationCopySubtreeData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationCopySubtreeType extends AbstractLocationCopyType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LocationCopySubtreeData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
