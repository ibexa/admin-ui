<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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

    /**
     * @var \Ibexa\Contracts\Core\Repository\LanguageService
     */
    private $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
        $this->logger = new NullLogger();
    }

    protected function getSelectionChoices()
    {
        $choices = [];
        foreach ($this->languageService->loadLanguages() as $language) {
            $choices[$language->languageCode] = $language->name;
        }

        return $choices;
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        $values = [];

        foreach ($limitation->limitationValues as $languageCode) {
            try {
                $values[] = $this->languageService->loadLanguage($languageCode);
            } catch (NotFoundException $e) {
                $this->logger->error(sprintf('Could not map the Limitation value: could not find a language with code %s', $languageCode));
            }
        }

        return $values;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(new Message(
                LimitationIdentifierToLabelConverter::convert('language'),
                'ezplatform_content_forms_policies'
            ))->setDesc('Language'),
        ];
    }
}

class_alias(LanguageLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\LanguageLimitationMapper');
