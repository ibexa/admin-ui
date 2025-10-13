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

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class SectionChoiceType extends AbstractType
{
    public function __construct(private readonly SectionService $sectionService)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->sectionService->loadSections(),
            'choice_label' => 'name',
            'choice_value' => 'id',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
