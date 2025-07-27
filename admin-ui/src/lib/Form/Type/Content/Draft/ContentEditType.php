<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content\Draft;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\LanguageChoiceLoader;
use Ibexa\AdminUi\Form\Type\Content\ContentInfoType;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\AdminUi\Form\Type\Content\VersionInfoType;
use Ibexa\AdminUi\Form\Type\Language\LanguageChoiceType;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentEditType extends AbstractType
{
    protected LanguageService $languageService;

    private LanguageChoiceLoader $languageChoiceLoader;

    public function __construct(
        LanguageService $languageService,
        LanguageChoiceLoader $languageChoiceLoader
    ) {
        $this->languageService = $languageService;
        $this->languageChoiceLoader = $languageChoiceLoader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'location',
                LocationType::class,
                ['label' => false, 'attr' => ['hidden' => true]]
            )
            ->add(
                'content_info',
                ContentInfoType::class,
                ['label' => false, 'attr' => ['hidden' => true]]
            )
            ->add(
                'version_info',
                VersionInfoType::class,
                ['label' => false]
            )
            ->add(
                'language',
                LanguageChoiceType::class,
                $this->getLanguageOptions($options)
            )
            ->add(
                'create',
                SubmitType::class,
                [
                    'attr' => ['hidden' => true],
                    'label' => /** @Desc("Create") */
                        'content_draft_create_type.create',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ContentEditData::class,
                'translation_domain' => 'forms',
                'language_codes' => false,
                'choice_loader' => $this->languageChoiceLoader,
            ])
            ->setAllowedTypes('language_codes', ['bool', 'array']);
    }

    private function getLanguageOptions(array $options): array
    {
        $languageOptions = [
            'label' => false,
            'multiple' => false,
            'expanded' => true,
            'choice_loader' => $options['choice_loader'],
        ];

        if ($options['choice_loader'] instanceof LanguageChoiceLoader && is_array($options['language_codes'])) {
            $languageOptions['choice_loader'] = new CallbackChoiceLoader(function () use ($options): array {
                return array_map([$this->languageService, 'loadLanguage'], $options['language_codes']);
            });
        }

        return $languageOptions;
    }
}
