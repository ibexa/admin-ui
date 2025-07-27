<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\Specification\UserProfile\IsProfileAvailable;
use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserProfileExtension extends AbstractExtension
{
    private UserProfileConfigurationInterface $configuration;

    public function __construct(UserProfileConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_is_user_profile_available',
                fn (User $user): bool => (new IsProfileAvailable($this->configuration))->isSatisfiedBy($user)
            ),
        ];
    }
}
