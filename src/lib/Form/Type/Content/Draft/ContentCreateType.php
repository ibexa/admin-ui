<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content\Draft;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentCreateData;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentCreateContentTypeChoiceLoader;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentCreateLanguageChoiceLoader;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\AdminUi\Form\Type\ContentType\ContentTypeChoiceType;
use Ibexa\AdminUi\Form\Type\Language\LanguageChoiceType;
use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentCreateType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    /** @var \Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface */
    private $contentCreateContentTypeChoiceLoader;

    /** @var \Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface */
    private $languageChoiceLoader;

    /** @var \Ibexa\AdminUi\Permission\LookupLimitationsTransformer */
    private $lookupLimitationsTransformer;

    /** @var \Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface */
    private $permissionChecker;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface $contentTypeChoiceLoader
     * @param \Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface $languageChoiceLoader
     * @param \Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface $permissionChecker
     * @param \Ibexa\AdminUi\Permission\LookupLimitationsTransformer $lookupLimitationsTransformer
     */
    public function __construct(
        LanguageService $languageService,
        ContentCreateContentTypeChoiceLoader $contentCreateContentTypeChoiceLoader,
        ChoiceLoaderInterface $languageChoiceLoader,
        PermissionCheckerInterface $permissionChecker,
        LookupLimitationsTransformer $lookupLimitationsTransformer
    ) {
        $this->languageService = $languageService;
        $this->contentCreateContentTypeChoiceLoader = $contentCreateContentTypeChoiceLoader;
        $this->languageChoiceLoader = $languageChoiceLoader;
        $this->permissionChecker = $permissionChecker;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $restrictedContentTypesIds = [];
        $restrictedLanguageCodes = [];

        /** @var \Ibexa\AdminUi\Form\Data\Content\Draft\ContentCreateData $contentCreateData */
        $contentCreateData = $options['data'];
        if ($location = $contentCreateData->getParentLocation()) {
            $limitationsValues = $this->getLimitationValuesForLocation($location);
            $restrictedContentTypesIds = $limitationsValues[Limitation::CONTENTTYPE];
            $restrictedLanguageCodes = $limitationsValues[Limitation::LANGUAGE];
        }

        $builder
            ->add(
                'content_type',
                ContentTypeChoiceType::class,
                [
                    'label' => false,
                    'multiple' => false,
                    'expanded' => true,
                    'choice_loader' => $this->contentCreateContentTypeChoiceLoader
                        ->setTargetLocation($location)
                        ->setRestrictedContentTypeIds($restrictedContentTypesIds),
                ]
            )
            ->add(
                'parent_location',
                LocationType::class,
                ['label' => false]
            )
            ->add(
                'language',
                LanguageChoiceType::class,
                [
                    'label' => false,
                    'multiple' => false,
                    'expanded' => false,
                    'choice_loader' => new ContentCreateLanguageChoiceLoader($this->languageChoiceLoader, $restrictedLanguageCodes),
                ]
            )
            ->add(
                'create',
                SubmitType::class,
                [
                    'label' => /** @Desc("Create") */
                        'content_draft_create_type.create',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ContentCreateData::class,
                'translation_domain' => 'forms',
            ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return array
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getLimitationValuesForLocation(Location $location): array
    {
        $lookupLimitationsResult = $this->permissionChecker->getContentCreateLimitations($location);

        return $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );
    }
}

class_alias(ContentCreateType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Content\Draft\ContentCreateType');
