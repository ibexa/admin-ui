<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UserSetting;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\User\UserSetting\FormMapperInterface;
use Ibexa\Contracts\User\UserSetting\ValueDefinitionInterface;
use Ibexa\Core\Repository\LanguageService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentLanguage implements ValueDefinitionInterface, FormMapperInterface
{
    private TranslatorInterface $translator;

    private LanguageService $languageService;

    public function __construct(
        TranslatorInterface $translator,
        LanguageService $languageService
    ) {
        $this->translator = $translator;
        $this->languageService = $languageService;
    }

    public function getName(): string
    {
        return $this->getTranslatedName();
    }

    public function getDescription(): string
    {
        return $this->getTranslatedDescription();
    }

    public function getDisplayValue(string $storageValue): string
    {
        try {
            $language = $this->languageService->loadLanguage($storageValue);
        } catch (\Exception $e) {
            return '';
        }

        return $language->getName();
    }

    public function getDefaultValue(): string
    {
        $contentLanguages = $this->languageService->loadLanguages();
        $firstContentLanguage = reset($contentLanguages);

        return $firstContentLanguage->getLanguageCode();
    }

    public function mapFieldForm(
        FormBuilderInterface $formBuilder,
        ValueDefinitionInterface $value
    ): FormBuilderInterface {
        $contentLanguages = array_reduce(
            $this->languageService->loadLanguages(),
            static function (array $choices, Language $language): array {
                $choices[$language->getName()] = $language->getLanguageCode();

                return $choices;
            },
            []
        );

        return $formBuilder->create(
            'value',
            ChoiceType::class,
            [
                'multiple' => false,
                'required' => true,
                'label' => $this->getTranslatedDescription(),
                'choices' => $contentLanguages,
            ]
        );
    }

    private function getTranslatedName(): string
    {
        return $this->translator->trans(
            /** @Desc("Content language") */
            'settings.content_language.value.title',
            [],
            'ibexa_user_settings'
        );
    }

    private function getTranslatedDescription(): string
    {
        return $this->translator->trans(
            /** @Desc("Choose your main content language") */
            'settings.content_language.value.description',
            [],
            'ibexa_user_settings'
        );
    }
}
