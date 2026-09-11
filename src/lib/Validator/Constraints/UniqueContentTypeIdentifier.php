<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class UniqueContentTypeIdentifier extends Constraint implements TranslationContainerInterface
{
    public string $message = 'ez.content_type.identifier.unique';

    /**
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct(null, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    /**
     * @return array<\JMS\TranslationBundle\Model\Message>
     */
    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ez.content_type.identifier.unique', 'validators')
                ->setDesc('The content type identifier "%identifier%" is used by another content type. Enter a unique identifier.'),
        ];
    }

    public function validatedBy(): string
    {
        return 'ezplatform.content_forms.validator.unique_content_type_identifier';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
