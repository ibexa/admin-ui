<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Resolver;

use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverInterface;
use Ibexa\Rest\Server\Values\RestFieldDefinition;
use Ibexa\Rest\Value;

final class ProfilePictureFieldConfigRestResolver implements ApplicationConfigRestResolverInterface
{
    private const NAMESPACE = 'user';
    private const PARAMETER = 'profile_picture_field';

    public function supportsNamespace(string $namespace): bool
    {
        return self::NAMESPACE === $namespace;
    }

    public function supportsParameter(string $parameterName): bool
    {
        return self::PARAMETER === $parameterName;
    }

    public function resolve(array $config): ?Value
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\User\User|null $apiUser */
        $apiUser = $config['user'];
        if (null === $apiUser) {
            return null;
        }

        $userContentType = $apiUser->getContentType();
        if (null === $config['profile_picture_field']) {
            return null;
        }

        $fieldDefinition = $userContentType->getFieldDefinition(
            $config['profile_picture_field']->fieldDefIdentifier
        );

        if (null === $fieldDefinition) {
            return null;
        }

        return new RestFieldDefinition($userContentType, $fieldDefinition);
    }
}
