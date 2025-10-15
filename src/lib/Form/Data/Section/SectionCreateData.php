<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Section;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo add validation
 */
final class SectionCreateData implements TranslationContainerInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^[[:alnum:]_]+$/', message: 'ez.section.identifier.format')]
        private ?string $identifier = null,
        #[Assert\NotBlank]
        private ?string $name = null
    ) {
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ez.section.identifier.format', 'validators')
                ->setDesc('Section identifier may only contain letters from "a" to "z", numbers and underscores.'),
        ];
    }
}
