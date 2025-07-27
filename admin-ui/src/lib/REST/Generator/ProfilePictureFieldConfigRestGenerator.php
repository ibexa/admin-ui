<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Generator;

use Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

final class ProfilePictureFieldConfigRestGenerator implements ApplicationConfigRestGeneratorInterface
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

    public function generate($parameter, Generator $generator, Visitor $visitor): void
    {
        if ($parameter instanceof Field) {
            $generator->startHashElement(self::PARAMETER);
            $visitor->visitValueObject($parameter);
            $generator->endHashElement(self::PARAMETER);

            return;
        }

        $generator->generateFieldTypeHash(self::PARAMETER, $parameter);
    }
}
