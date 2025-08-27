<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class LanguageLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    use LoggerAwareTrait;

    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
        $this->logger = new NullLogger();
    }

    /**
     * @return mixed[]
     */
    protected function getSelectionChoices(): array
    {
        $choices = [];
        foreach ($this->languageService->loadLanguages() as $language) {
            $choices[$language->languageCode] = $language->name;
        }

        return $choices;
    }

    /**
     * @return mixed[]
     */
    public function mapLimitationValue(Limitation $limitation): array
    {
        $values = [];

        foreach ($limitation->limitationValues as $languageCode) {
            try {
                $values[] = $this->languageService->loadLanguage($languageCode);
            } catch (NotFoundException) {
                $this->logger?->error(
                    sprintf('Could not map the Limitation value: could not find a language with code %s', $languageCode)
                );
            }
        }

        return $values;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('language'),
                'ibexa_content_forms_policies'
            )->setDesc('Language'),
        ];
    }
}
