<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Section;

use Ibexa\Contracts\Core\Repository\SectionService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionChoiceType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\SectionService */
    private $sectionService;

    /**
     * SectionChoiceType constructor.
     *
     * @param \Ibexa\Contracts\Core\Repository\SectionService $sectionService
     */
    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->sectionService->loadSections(),
            'choice_label' => 'name',
            'choice_value' => 'id',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}

class_alias(SectionChoiceType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Section\SectionChoiceType');
