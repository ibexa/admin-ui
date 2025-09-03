<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class UniqueFieldDefinitionIdentifier extends Constraint implements TranslationContainerInterface
{
    public string $message = 'ez.field_definition.identifier.unique';

    /**
     * @return array<\JMS\TranslationBundle\Model\Message>
     */
    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ez.field_definition.identifier.unique', 'validators')
                ->setDesc('The Field definition identifier "%identifier%" is used by another Field definition. Enter a unique identifier.'),
        ];
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
