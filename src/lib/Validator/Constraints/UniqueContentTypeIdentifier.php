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

    public static function getTranslationMessages()
    {
        return [
            Message::create('ez.content_type.identifier.unique', 'validators')
                ->setDesc('The Content Type identifier "%identifier%" is used by another Content Type. Enter a unique identifier.'),
        ];
    }

    public function validatedBy()
    {
        return 'ezplatform.content_forms.validator.unique_content_type_identifier';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

class_alias(UniqueContentTypeIdentifier::class, 'EzSystems\EzPlatformAdminUi\Validator\Constraints\UniqueContentTypeIdentifier');
