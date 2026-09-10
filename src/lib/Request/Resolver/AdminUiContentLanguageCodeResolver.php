<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Request\Resolver;

use Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Symfony\Component\HttpFoundation\Request;

final class AdminUiContentLanguageCodeResolver implements ContentLanguageCodeResolverInterface
{
    public function resolve(Request $request): ?string
    {
        $language = $request->attributes->get('language');
        if ($language instanceof Language) {
            return $language->getLanguageCode();
        }

        if (is_string($language) && $language !== '') {
            return $language;
        }

        $languageCode = $request->attributes->get('languageCode');
        if (is_string($languageCode) && $languageCode !== '') {
            return $languageCode;
        }

        return null;
    }
}
