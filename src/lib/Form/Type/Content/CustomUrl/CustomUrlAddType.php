<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content\CustomUrl;

use Ibexa\AdminUi\Form\EventListener\AddLanguageFieldBasedOnContentListener;
use Ibexa\AdminUi\Form\EventListener\BuildPathFromRootListener;
use Ibexa\AdminUi\Form\EventListener\DisableSiteRootCheckboxIfRootLocationListener;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\SiteAccessChoiceLoader;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\AdminUi\Siteaccess\SiteAccessNameGeneratorInterface;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\Contracts\Core\Repository\LanguageService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomUrlAddType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    /** @var \Ibexa\AdminUi\Form\EventListener\AddLanguageFieldBasedOnContentListener */
    private $addLanguageFieldBasedOnContentListener;

    /** @var \Ibexa\AdminUi\Form\EventListener\BuildPathFromRootListener */
    private $buildPathFromRootListener;

    /** @var \Ibexa\AdminUi\Form\EventListener\DisableSiteRootCheckboxIfRootLocationListener */
    private $checkboxIfRootLocationListener;

    /** @var \Ibexa\AdminUi\Siteaccess\NonAdminSiteaccessResolver */
    private $nonAdminSiteaccessResolver;

    private SiteAccessNameGeneratorInterface $siteAccessNameGenerator;

    public function __construct(
        LanguageService $languageService,
        AddLanguageFieldBasedOnContentListener $addLanguageFieldBasedOnContentListener,
        BuildPathFromRootListener $buildPathFromRootListener,
        DisableSiteRootCheckboxIfRootLocationListener $checkboxIfRootLocationListener,
        SiteaccessResolverInterface $nonAdminSiteaccessResolver,
        SiteAccessNameGeneratorInterface $siteAccessNameGenerator
    ) {
        $this->languageService = $languageService;
        $this->addLanguageFieldBasedOnContentListener = $addLanguageFieldBasedOnContentListener;
        $this->buildPathFromRootListener = $buildPathFromRootListener;
        $this->checkboxIfRootLocationListener = $checkboxIfRootLocationListener;
        $this->nonAdminSiteaccessResolver = $nonAdminSiteaccessResolver;
        $this->siteAccessNameGenerator = $siteAccessNameGenerator;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $location = $options['data']->getLocation();

        $builder
            ->add(
                'location',
                LocationType::class,
                ['label' => false]
            )
            ->add(
                'path',
                TextType::class,
                ['label' => false]
            )
            ->add(
                'language',
                ChoiceType::class,
                [
                    'multiple' => false,
                    'choice_loader' => new CallbackChoiceLoader([$this->languageService, 'loadLanguages']),
                    'choice_value' => 'languageCode',
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'redirect',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => false,
                ]
            )
            ->add(
                'site_root',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => false,
                ]
            )
            ->add(
                'site_access',
                ChoiceType::class,
                [
                    'required' => false,
                    'choice_loader' => new SiteAccessChoiceLoader(
                        $this->nonAdminSiteaccessResolver,
                        $this->siteAccessNameGenerator,
                        $location
                    ),
                    'placeholder' => /** @Desc("None") */ 'custom_url_alias_add_form.site_access.placeholder',
                ]
            )
            ->add(
                'add',
                SubmitType::class,
                [
                    'label' => /** @Desc("Create") */ 'custom_url_alias_add_form.add',
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [
            $this->addLanguageFieldBasedOnContentListener,
            'onPreSetData',
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [
            $this->buildPathFromRootListener,
            'onPreSubmitData',
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [
            $this->checkboxIfRootLocationListener,
            'onPreSetData',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_content_url',
        ]);
    }
}
