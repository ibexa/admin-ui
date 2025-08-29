<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Language;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class LanguageCreateData implements TranslationContainerInterface
{
    #[Assert\NotBlank]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_][a-zA-Z0-9_\-:]*$/', message: 'ibexa.language.language_code.format')]
    private ?string $languageCode = null;

    private bool $enabled = true;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function setLanguageCode(?string $languageCode): self
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

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
