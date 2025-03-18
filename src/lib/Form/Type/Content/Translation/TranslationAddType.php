<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content\Translation;

use Ibexa\AdminUi\Form\Data\Content\Translation\TranslationAddData;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationAddType extends AbstractType
{
    protected LanguageService $languageService;

    protected ContentService $contentService;

    protected LocationService $locationService;

    private PermissionResolver $permissionResolver;

    private LookupLimitationsTransformer $lookupLimitationsTransformer;

    public function __construct(
        LanguageService $langaugeService,
        ContentService $contentService,
        LocationService $locationService,
        PermissionResolver $permissionResolver,
        LookupLimitationsTransformer $lookupLimitationsTransformer
    ) {
        $this->languageService = $langaugeService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->permissionResolver = $permissionResolver;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'location',
                LocationType::class,
                ['label' => false]
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TranslationAddData::class,
            'translation_domain' => 'forms',
        ]);
    }

    /**
     * Adds language fields and populates options list based on default form data.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function onPreSetData(FormEvent $event): void
    {
        $contentInfo = null;
        $contentLanguages = [];
        $form = $event->getForm();
        $data = $event->getData();
        $location = $data->getLocation();

        if (null !== $location) {
            $contentInfo = $location->getContentInfo();
            $versionInfo = $this->contentService->loadVersionInfo($contentInfo);
            $contentLanguages = $versionInfo->languageCodes;
        }

        $this->addLanguageFields($form, $contentLanguages, $contentInfo, $location);
    }

    /**
     * Adds language fields and populates options list based on submitted form data.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $contentInfo = null;
        $contentLanguages = [];
        $form = $event->getForm();
        $data = $event->getData();

        $location = null;
        if (isset($data['location'])) {
            try {
                $location = $this->locationService->loadLocation((int)$data['location']);
            } catch (NotFoundException $e) {
                $location = null;
            }

            if (null !== $location) {
                $contentInfo = $location->getContentInfo();
                $versionInfo = $this->contentService->loadVersionInfo($contentInfo);
                $contentLanguages = $versionInfo->languageCodes;
            }
        }

        $this->addLanguageFields($form, $contentLanguages, $contentInfo, $location);
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
     * @param string[] $contentLanguages
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function addLanguageFields(
        FormInterface $form,
        array $contentLanguages,
        ?ContentInfo $contentInfo,
        ?Location $location = null
    ): void {
        $languagesCodes = array_column($this->languageService->loadLanguages(), 'languageCode');

        $limitationLanguageCodes = [];
        if (null !== $contentInfo) {
            $lookupLimitations = $this->permissionResolver->lookupLimitations(
                'content',
                'edit',
                $contentInfo,
                [
                    (new Target\Builder\VersionBuilder())->translateToAnyLanguageOf($languagesCodes)->build(),
                    $this->locationService->loadLocation(
                        $location !== null
                            ? $location->id
                            : $contentInfo->mainLocationId
                    ),
                ],
                [Limitation::LANGUAGE]
            );

            $limitationLanguageCodes = $this->lookupLimitationsTransformer->getFlattenedLimitationsValues($lookupLimitations);
        }

        $form
            ->add(
                'language',
                ChoiceType::class,
                [
                    'required' => true,
                    'placeholder' => false,
                    'multiple' => false,
                    'expanded' => false,
                    'choice_loader' => new CallbackChoiceLoader(function () use ($contentLanguages, $limitationLanguageCodes): array {
                        return $this->loadLanguages(
                            static function (Language $language) use ($contentLanguages, $limitationLanguageCodes): bool {
                                return $language->enabled
                                    && !in_array($language->languageCode, $contentLanguages, true)
                                    && (empty($limitationLanguageCodes) || in_array($language->languageCode, $limitationLanguageCodes, true));
                            }
                        );
                    }),
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
                    'choice_loader' => new CallbackChoiceLoader(function () use ($contentLanguages): array {
                        return $this->loadLanguages(
                            static function (Language $language) use ($contentLanguages): bool {
                                return $language->enabled && in_array($language->languageCode, $contentLanguages, true);
                            }
                        );
                    }),
                    'label' => /** @Desc("Source language") */ 'translation.base_language.label',
                    'choice_value' => 'languageCode',
                    'choice_label' => 'name',
                ]
            );
    }
}
