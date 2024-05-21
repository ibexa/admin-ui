<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\User\Form\ChoiceList\Loader\UserGroupsChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserGroupChoiceType extends AbstractType
{
    private Repository $repository;

    private SearchService $searchService;

    private UserService $userService;

    public function __construct(
        Repository $repository,
        SearchService $searchService,
        UserService $userService
    ) {
        $this->repository = $repository;
        $this->searchService = $searchService;
        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_loader' => new UserGroupsChoiceLoader(
                $this->repository,
                $this->searchService,
                $this->userService
            ),
            'choice_label' => 'name',
            'choice_value' => 'id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
