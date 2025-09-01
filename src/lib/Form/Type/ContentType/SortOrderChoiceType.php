<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type for sort order selection.
 *
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class SortOrderChoiceType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->getSortOrderChoices(),
            'translation_domain' => 'ibexa_content_type',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * Generate sort order options available to choose.
     *
     * @return array<string, int>
     */
    private function getSortOrderChoices(): array
    {
        $choices = [];
        foreach ($this->getSortOrder() as $label => $value) {
            $choices[$label] = $value;
        }

        return $choices;
    }

    /**
     * Get available sort order values.
     *
     * @return array<string, int>
     */
    private function getSortOrder(): array
    {
        return [
            $this->translator->trans(/** @Desc("Ascending") */
                'content_type.sort_field.ascending',
                [],
                'ibexa_content_type'
            ) => Location::SORT_ORDER_ASC,
            $this->translator->trans(/** @Desc("Descending") */
                'content_type.sort_field.descending',
                [],
                'ibexa_content_type'
            ) => Location::SORT_ORDER_DESC,
        ];
    }
}
