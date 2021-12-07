<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Embedded;

use Ibexa\AdminUi\Form\DataTransformer\SectionsTransformer;
use Ibexa\AdminUi\Form\DataTransformer\SectionTransformer;
use Ibexa\Contracts\Core\Repository\SectionService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\SectionService */
    protected $sectionService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\SectionService $sectionService
     */
    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            $options['multiple']
            ? new SectionsTransformer($this->sectionService)
            : new SectionTransformer($this->sectionService)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('multiple', false);
        $resolver->setRequired(['multiple']);
        $resolver->setAllowedTypes('multiple', 'boolean');
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}

class_alias(SectionType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Embedded\SectionType');
