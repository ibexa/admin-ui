<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\User\User as ApiUser;
use Ibexa\Core\MVC\Symfony\Security\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Provides information about current user with resolved profile picture.
 */
final readonly class User implements ProviderInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Returns configuration structure compatible with PlatformUI.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = ['user' => null, 'profile_picture_field' => null];

        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            return $config;
        }

        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            $apiUser = $user->getAPIUser();
            $config['user'] = $apiUser;
            $config['profile_picture_field'] = $this->resolveProfilePictureField($apiUser);
        }

        return $config;
    }

    /**
     * Returns first occurrence of an `ibexa_image` fieldtype.
     */
    private function resolveProfilePictureField(ApiUser $user): ?Field
    {
        $contentType = $user->getContentType();
        foreach ($user->getFields() as $field) {
            $fieldDefinition = $contentType->getFieldDefinition(
                $field->getFieldDefinitionIdentifier()
            );

            if ($fieldDefinition === null) {
                continue;
            }

            if ('ibexa_image' === $fieldDefinition->getFieldTypeIdentifier()) {
                return $field;
            }
        }

        return null;
    }
}
