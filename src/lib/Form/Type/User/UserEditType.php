<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\User;

use Ibexa\AdminUi\Form\Data\User\UserEditData;
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

class UserEditType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content_info',
                ContentInfoType::class,
                ['label' => false, 'attr' => ['hidden' => true]]
            )
            ->add(
                'location',
                LocationType::class,
                ['label' => false, 'attr' => ['hidden' => true]]
            )
            ->add(
                'version_info',
                VersionInfoType::class,
                ['label' => false, 'attr' => ['hidden' => true]]
            )
            ->add(
                'language',
                LanguageChoiceType::class,
                $this->getLanguageOptions($options)
            )
            ->add(
                'edit',
                SubmitType::class,
                ['attr' => ['hidden' => true]]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => UserEditData::class,
                'translation_domain' => 'forms',
                'language_codes' => false,
            ])
            ->setAllowedTypes('language_codes', ['bool', 'array']);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getLanguageOptions(array $options): array
    {
        $languageOptions = [
            'label' => false,
            'multiple' => false,
            'expanded' => true,
        ];

        if (is_array($options['language_codes'])) {
            $languageOptions['choice_loader'] = new CallbackChoiceLoader(function () use ($options) {
                return array_map([$this->languageService, 'loadLanguage'], $options['language_codes']);
            });
        }

        return $languageOptions;
    }
}
