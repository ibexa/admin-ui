<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\User;

use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\AdminUi\Form\DataTransformer\UserTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    protected $userService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new UserTransformer($this->userService));
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}

class_alias(UserType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\User\UserType');
