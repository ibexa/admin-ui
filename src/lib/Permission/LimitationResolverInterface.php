<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @internal
 */
interface LimitationResolverInterface
{
    /**
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getContentCreateLimitations(Location $parentLocation): LookupLimitationResult;

    /**
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getContentUpdateLimitations(Location $parentLocation): LookupLimitationResult;

    /**
     * @param iterable<Language> $languages
     * @param array<ValueObject> $targets
     *
     * @return array<array{
     *     languageCode: string,
     *     name: string,
     *     hasAccess: bool,
     * }>
     */
    public function getLanguageLimitations(
        string $function,
        ValueObject $valueObject,
        iterable $languages = [],
        array $targets = []
    ): array;
}
