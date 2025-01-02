<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\Content\Language>
 */
final class LanguageFromCodeValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_LANGUAGE_CORE = 'languageCode';

    public function __construct(
        private readonly LanguageService $languageService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_LANGUAGE_CORE];
    }

    protected function getClass(): string
    {
        return Language::class;
    }

    protected function validateValue(string $value): bool
    {
        return $value !== '';
    }

    protected function load(array $key): object
    {
        return $this->languageService->loadLanguage(
            $key[self::ATTRIBUTE_LANGUAGE_CORE]
        );
    }
}
