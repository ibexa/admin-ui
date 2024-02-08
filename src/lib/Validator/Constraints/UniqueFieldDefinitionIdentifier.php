<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueFieldDefinitionIdentifier extends Constraint implements TranslationContainerInterface
{
    /**
     * %identifier% placeholder is passed.
     *
     * @var string
     */
    public $message = 'ez.field_definition.identifier.unique';

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

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

class_alias(UniqueFieldDefinitionIdentifier::class, 'EzSystems\EzPlatformAdminUi\Validator\Constraints\UniqueFieldDefinitionIdentifier');
