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
class UniqueContentTypeIdentifier extends Constraint implements TranslationContainerInterface
{
    /**
     * %identifier% placeholder is passed.
     *
     * @var string
     */
    public $message = 'ez.content_type.identifier.unique';

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

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
