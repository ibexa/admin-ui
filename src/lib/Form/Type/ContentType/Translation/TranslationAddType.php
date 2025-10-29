<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentType\Translation;

use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\AvailableTranslationLanguageChoiceLoader;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\BaseTranslationLanguageChoiceLoader;
use Ibexa\AdminUi\Form\Type\Content\ContentTypeType;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupType;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationAddType extends AbstractType
{
    /** @var LanguageService */
    protected $languageService;

    /** @var ContentTypeService */
    private $contentTypeService;

    /**
     * @param LanguageService $languageService
     * @param ContentTypeService $contentTypeService
     */
    public function __construct(
        LanguageService $languageService,
        ContentTypeService $contentTypeService
    ) {
        $this->languageService = $languageService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        $builder
            ->add(
                'contentType',
                ContentTypeType::class,
                [
                    'label' => false,
                    'attr' => [
                        'hidden' => true,
                    ],
                ]
            )
            ->add(
                'contentTypeGroup',
                ContentTypeGroupType::class
            )
            ->add(
                'add',
                SubmitType::class,
                [
                    'label' => /** @Desc("Create") */ 'content_translation_add_form.add',
                ]
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TranslationAddData::class,
            'translation_domain' => 'forms',
        ]);
    }

    /**
     * Adds language fields and populates options list based on default form data.
     *
     * @param FormEvent $event
     *
     * @throws UnauthorizedException
     * @throws NotFoundException
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     */
    public function onPreSetData(FormEvent $event)
    {
        $contentLanguages = [];
        $form = $event->getForm();

        /** @var TranslationAddData $data */
        $data = $event->getData();
        $contentType = $data->getContentType();

        if (null !== $contentType) {
            $contentLanguages = array_keys($contentType->getNames());
        }

        $this->addLanguageFields($form, $contentLanguages);
    }

    /**
     * Adds language fields and populates options list based on submitted form data.
     *
     * @param FormEvent $event
     *
     * @throws UnauthorizedException
     * @throws NotFoundException
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     */
    public function onPreSubmit(FormEvent $event)
    {
        $contentLanguages = [];
        $form = $event->getForm();
        $data = $event->getData();

        if (isset($data['contentType'])) {
            try {
                $contentType = $this->contentTypeService->loadContentTypeByIdentifier($data['contentType']);
            } catch (NotFoundException $e) {
                $contentType = null;
            }

            if (null !== $contentType) {
                $contentLanguages = array_keys($contentType->getNames());
            }
        }

        $this->addLanguageFields($form, $contentLanguages);
    }

    /**
     * Loads system languages with filtering applied.
     *
     * @param callable $filter
     *
     * @return array
     */
    public function loadLanguages(callable $filter): array
    {
        return array_filter(
            $this->languageService->loadLanguages(),
            $filter
        );
    }

    /**
     * Adds language fields to the $form. Language options are composed based on content language.
     *
     * @param FormInterface $form
     * @param string[] $contentLanguages
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     */
    public function addLanguageFields(
        FormInterface $form,
        array $contentLanguages
    ): void {
        $form
            ->add(
                'language',
                ChoiceType::class,
                [
                    'required' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'choice_loader' => new AvailableTranslationLanguageChoiceLoader($this->languageService, $contentLanguages),
                    'label' => /** @Desc("Target language") */ 'translation.language.label',
                    'choice_value' => 'languageCode',
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'base_language',
                ChoiceType::class,
                [
                    'required' => false,
                    'placeholder' => /** @Desc("No language") */ 'translation.base_language.no_language',
                    'multiple' => false,
                    'expanded' => false,
                    'choice_loader' => new BaseTranslationLanguageChoiceLoader($this->languageService, $contentLanguages),
                    'label' => /** @Desc("Source language") */ 'translation.base_language.label',
                    'choice_value' => 'languageCode',
                    'choice_label' => 'name',
                ]
            );
    }
}

class_alias(TranslationAddType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\ContentType\Translation\TranslationAddType');
