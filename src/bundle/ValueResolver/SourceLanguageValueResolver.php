<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Persistence\Content\Language;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @template-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Persistence\Content\Language>
 */
final class SourceLanguageValueResolver extends AbstractValueResolver
{
    private const string ARGUMENT_NAME = 'language';
    private const string ATTRIBUTE_LANGUAGE_CODE_FROM = 'fromLanguageCode';

    public function __construct(
        private readonly LanguageService $languageService
    ) {
    }

    protected function supports(ArgumentMetadata $argument): bool
    {
        if ($argument->getName() !== self::ARGUMENT_NAME) {
            return false;
        }

        return parent::supports($argument);
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_LANGUAGE_CODE_FROM];
    }

    protected function getClass(): string
    {
        return Language::class;
    }

    protected function load(array $key): object
    {
        return $this->languageService->loadLanguage(
            $key[self::ATTRIBUTE_LANGUAGE_CODE_FROM]
        );
    }
}
