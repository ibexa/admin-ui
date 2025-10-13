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

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class UserGroupChoiceType extends AbstractType
{
    public function __construct(
        private readonly Repository $repository,
        private readonly SearchService $searchService,
        private readonly UserService $userService
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
