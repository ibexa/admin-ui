<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Language;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractLanguageChoiceType extends AbstractType
{
    private ChoiceLoaderInterface $languageChoiceLoader;

    /**
     * @param \Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface $languageChoiceLoader
     */
    public function __construct(ChoiceLoaderInterface $languageChoiceLoader)
    {
        $this->languageChoiceLoader = $languageChoiceLoader;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_loader' => $this->languageChoiceLoader,
                'choice_label' => 'name',
                'choice_name' => 'languageCode',
                'choice_value' => 'languageCode',
            ]);
    }
}
