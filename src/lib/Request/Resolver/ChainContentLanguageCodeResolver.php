<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Request\Resolver;

use Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class ChainContentLanguageCodeResolver implements ContentLanguageCodeResolverInterface
{
    /** @var iterable<\Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface> */
    private iterable $resolvers;

    /**
     * @param iterable<\Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface> $resolvers
     */
    public function __construct(iterable $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolve(Request $request): ?string
    {
        foreach ($this->resolvers as $resolver) {
            $languageCode = $resolver->resolve($request);

            if (is_string($languageCode) && $languageCode !== '') {
                return $languageCode;
            }
        }

        return null;
    }
}
