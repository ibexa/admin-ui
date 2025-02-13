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
use Ibexa\AdminUi\Permission\LimitationResolverInterface;
use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
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

    private ContentCreateContentTypeChoiceLoader $contentCreateContentTypeChoiceLoader;

    /** @var \Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface */
    private $languageChoiceLoader;

    /** @var \Ibexa\AdminUi\Permission\LookupLimitationsTransformer */
    private $lookupLimitationsTransformer;

    private LimitationResolverInterface $limitationResolver;

    public function __construct(
        LanguageService $languageService,
        ContentCreateContentTypeChoiceLoader $contentCreateContentTypeChoiceLoader,
        ChoiceLoaderInterface $languageChoiceLoader,
        LookupLimitationsTransformer $lookupLimitationsTransformer,
        LimitationResolverInterface $limitationResolver
    ) {
        $this->languageService = $languageService;
        $this->contentCreateContentTypeChoiceLoader = $contentCreateContentTypeChoiceLoader;
        $this->languageChoiceLoader = $languageChoiceLoader;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
        $this->limitationResolver = $limitationResolver;
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    public function configureOptions(OptionsResolver $resolver): void
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
        $lookupLimitationsResult = $this->limitationResolver->getContentCreateLimitations($location);

        return $this->lookupLimitationsTransformer->getGroupedLimitationValues(
            $lookupLimitationsResult,
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );
    }
}
