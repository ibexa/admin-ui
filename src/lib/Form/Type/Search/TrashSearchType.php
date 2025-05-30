<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Search;

use Ibexa\AdminUi\Form\Data\Search\TrashSearchData;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\DatePeriodChoiceLoader;
use Ibexa\AdminUi\Form\Type\Content\SortType;
use Ibexa\AdminUi\Form\Type\Date\DateIntervalType;
use Ibexa\AdminUi\Form\Type\Section\SectionChoiceType;
use Ibexa\AdminUi\Form\Type\Trash\ChoiceList\Loader\SearchContentTypeChoiceLoader;
use Ibexa\AdminUi\Form\Type\User\UserType;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrashSearchType extends AbstractType
{
    private TranslatorInterface $translator;

    private PermissionResolver $permissionResolver;

    private DatePeriodChoiceLoader $datePeriodChoiceLoader;

    private SearchContentTypeChoiceLoader $searchContentTypeChoiceLoader;

    public function __construct(
        TranslatorInterface $translator,
        PermissionResolver $permissionResolver,
        DatePeriodChoiceLoader $datePeriodChoiceLoader,
        SearchContentTypeChoiceLoader $searchContentTypeChoiceLoader
    ) {
        $this->translator = $translator;
        $this->permissionResolver = $permissionResolver;
        $this->datePeriodChoiceLoader = $datePeriodChoiceLoader;
        $this->searchContentTypeChoiceLoader = $searchContentTypeChoiceLoader;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('page', HiddenType::class)
            ->add('content_name', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => /** @Desc("Search by content name") */ 'trash.search.search_by_content_name',
                ],
            ])
            ->add('content_type', ChoiceType::class, [
                'choice_loader' => $this->searchContentTypeChoiceLoader,
                'choice_label' => 'name',
                'choice_name' => 'identifier',
                'choice_value' => 'identifier',
                'required' => false,
                'placeholder' => /** @Desc("All") */ 'trash.search.any_content_types',
            ])
            ->add('creator', UserType::class)
            ->add('trashed_interval', DateIntervalType::class)
            ->add('trashed', ChoiceType::class, [
                'choice_loader' => $this->datePeriodChoiceLoader,
                'required' => false,
                'placeholder' => /** @Desc("Any time") */ 'trash.search.any_time',
                'mapped' => false,
            ])
            ->add('sort', SortType::class, [
                'sort_fields' => ['name', 'content_type', 'creator', 'section', 'parent_location', 'trashed'],
                'default' => ['field' => 'trashed', 'direction' => '1'],
            ])
        ;

        if ($this->permissionResolver->hasAccess('section', 'view') !== false) {
            $builder->add('section', SectionChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'placeholder' => /** @Desc("All") */ 'trash.search.section.any',
            ]);
        }
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrashSearchData::class,
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'translation_domain' => 'ibexa_trash',
        ]);
    }
}
