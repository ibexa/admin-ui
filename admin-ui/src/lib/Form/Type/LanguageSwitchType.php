<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type;

use Ibexa\AdminUi\Form\Data\AbstractLanguageSwitchData;
use Ibexa\AdminUi\Form\Type\Language\ConfiguredLanguagesChoiceType;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LanguageSwitchType extends AbstractType
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $languages = $options['languages'];

        $builder->add(
            'language',
            ConfiguredLanguagesChoiceType::class,
            [
                'choice_loader' => ChoiceList::lazy(
                    $this,
                    function () use ($languages): iterable {
                        return $this->languageService->loadLanguageListByCode($languages);
                    },
                    $languages
                ),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => AbstractLanguageSwitchData::class,
            'method' => Request::METHOD_GET,
        ]);

        $resolver->setRequired('languages');
    }
}
