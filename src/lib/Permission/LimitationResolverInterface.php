<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @internal
 */
interface LimitationResolverInterface
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getContentCreateLimitations(Location $parentLocation): LookupLimitationResult;

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getContentUpdateLimitations(Location $location): LookupLimitationResult;

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getContentDeleteLimitations(Location $location): LookupLimitationResult;

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getContentHideLimitations(Location $location): LookupLimitationResult;

    /**
     * @param iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Language> $languages
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ValueObject> $targets
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
