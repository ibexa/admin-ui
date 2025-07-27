<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard>
 */
final class URLWildcardValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_URL_WILDCARD_ID = 'urlWildcardId';

    public function __construct(
        private readonly URLWildcardService $urlWildcardService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_URL_WILDCARD_ID];
    }

    protected function getClass(): string
    {
        return URLWildcard::class;
    }

    protected function validateValue(string $value): bool
    {
        return is_numeric($value);
    }

    protected function load(array $key): object
    {
        return $this->urlWildcardService->load(
            (int)$key[self::ATTRIBUTE_URL_WILDCARD_ID]
        );
    }
}
