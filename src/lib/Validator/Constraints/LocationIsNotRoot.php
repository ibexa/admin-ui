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

class LocationIsNotRoot extends Constraint implements TranslationContainerInterface
{
    public string $message = 'ezplatform.copy_subtree.is_root';

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ezplatform.copy_subtree.is_root', 'validators')
                ->setDesc('Selected Location cannot be root Location.'),
        ];
    }
}
