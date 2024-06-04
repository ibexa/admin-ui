<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\AdminUi\Form\Data\ContentType\ContentTypeEditData;
use Ibexa\AdminUi\Form\Type\Content\ContentTypeType;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupType;
use Ibexa\AdminUi\Form\Type\Language\LanguageChoiceType;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeEditType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    public function __construct(
        LanguageService $languageService
    ) {
        $this->languageService = $languageService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType */
        $contentType = $options['contentType'];
        $contentTypeLanguages = $contentType->languageCodes;

        $builder
            ->add(
                'content_type',
                ContentTypeType::class,
                [
                    'label' => false,
                    'attr' => [
                        'hidden' => true,
                    ],
                ]
            )
            ->add(
                'content_type_group',
                ContentTypeGroupType::class
            )
            ->add(
                'language',
                LanguageChoiceType::class,
                [
                    'required' => true,
                    'label' => false,
                    'multiple' => false,
                    'expanded' => true,
                    'choice_loader' => new CallbackChoiceLoader(function () use ($contentTypeLanguages) {
                        return array_map([$this->languageService, 'loadLanguage'], $contentTypeLanguages);
                    }),
                ]
            )
            ->add(
                'add',
                SubmitType::class,
                [
                    'attr' => ['hidden' => true],
                    'label' => /** @Desc("Create") */ 'content_translation_add_form.add',
                ]
            );
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ContentTypeEditData::class,
                'translation_domain' => 'forms',
            ])
            ->setRequired('contentType')
            ->setAllowedTypes('contentType', ContentType::class);
    }
}
