<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data\Language;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class LanguageCreateData implements TranslationContainerInterface
{
    #[Assert\NotBlank]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_][a-zA-Z0-9_\-:]*$/', message: 'ibexa.language.language_code.format')]
    private ?string $languageCode = null;

    private bool $enabled = true;

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return LanguageCreateData
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    /**
     * @param string $languageCode
     *
     * @return LanguageCreateData
     */
    public function setLanguageCode(string $languageCode): self
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return LanguageCreateData
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ibexa.language.language_code.format', 'validators')
                ->setDesc(
                    'The Language code {{ value }} contains illegal characters. Language code should start with a letter, digit or underscore and only contain letters, digits, numbers, underscores, hyphens and colons.'
                ),
        ];
    }
}
