<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraint;

final class DateRangeConstraint extends Constraint implements TranslationContainerInterface
{
    public string $message = 'ibexa.date_range.invalid_range';

    /**
     * @return array<\JMS\TranslationBundle\Model\Message>
     */
    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ibexa.date_range.invalid_range', 'validators')
                ->setDesc('The From date must be earlier than the To date.'),
        ];
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
