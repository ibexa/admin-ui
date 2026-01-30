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
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\Content\Translation\TranslationAddData>
 */
class TranslationAddType extends AbstractType
{
    public function __construct(
        protected readonly LanguageService $languageService,
        protected readonly ContentService $contentService,
        protected readonly LocationService $locationService,
        private readonly PermissionResolver $permissionResolver,
        private readonly LookupLimitationsTransformer $lookupLimitationsTransformer
    ) {
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
            'allow_no_language' => true,
        ]);
        $resolver->setAllowedTypes('allow_no_language', 'bool');
    }

    /**
     * Adds language fields and populates options list based on default form data.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     */
    public function onPreSetData(FormEvent $event): void
    {
        $contentInfo = null;
        $contentLanguages = [];
        $form = $event->getForm();
        $data = $event->getData();
        $location = $data->getLocation();
        $options = $form->getConfig()->getOptions();

        if (null !== $location) {
            $contentInfo = $location->getContentInfo();
            $versionInfo = $this->contentService->loadVersionInfo($contentInfo);
            $contentLanguages = $versionInfo->languageCodes;
        }

        $this->addLanguageFields($form, $contentLanguages, $contentInfo, $location, $options);
    }

    /**
     * Adds language fields and populates options list based on submitted form data.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Symfony\Component\Form\Exception\AlreadySubmittedException
     * @throws \Symfony\Component\Form\Exception\LogicException
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $contentInfo = null;
        $contentLanguages = [];
        $form = $event->getForm();
        $data = $event->getData();
        $options = $form->getConfig()->getOptions();

        $location = null;
        if (isset($data['location'])) {
            try {
                $location = $this->locationService->loadLocation((int)$data['location']);
            } catch (NotFoundException) {
                // do nothing, location will remain null
            }

            if (null !== $location) {
                $contentInfo = $location->getContentInfo();
                $versionInfo = $this->contentService->loadVersionInfo($contentInfo);
                $contentLanguages = $versionInfo->getLanguageCodes();
            }
        }

        $this->addLanguageFields($form, $contentLanguages, $contentInfo, $location, $options);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language[]
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
     * @param array<string, mixed> $options
     */
    public function addLanguageFields(
        FormInterface $form,
        array $contentLanguages,
        ?ContentInfo $contentInfo,
        ?Location $location = null,
        array $options = []
    ): void {
        $allowNoLanguage = $options['allow_no_language'] ?? true;
        $languagesCodes = array_column(
            iterator_to_array($this->languageService->loadLanguages()),
            'languageCode'
        );

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
                            ? $location->getId()
                            : $contentInfo->getMainLocationId()
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
                                return $language->isEnabled()
                                    && !in_array($language->getLanguageCode(), $contentLanguages, true)
                                    && (empty($limitationLanguageCodes) || in_array($language->getLanguageCode(), $limitationLanguageCodes, true));
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
                    'placeholder' => $allowNoLanguage
                        ? /** @Desc("No language") */ 'translation.base_language.no_language'
                        : false,
                    'multiple' => false,
                    'expanded' => false,
                    'choice_loader' => new CallbackChoiceLoader(function () use ($contentLanguages): array {
                        return $this->loadLanguages(
                            static function (Language $language) use ($contentLanguages): bool {
                                return $language->isEnabled() && in_array($language->getLanguageCode(), $contentLanguages, true);
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
