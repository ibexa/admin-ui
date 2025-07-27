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

class LocationHaveUniqueAssetRelation extends Constraint implements TranslationContainerInterface
{
    public string $message = 'ezplatform.trash.have_used_assets';

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ezplatform.trash.have_used_assets', 'validators')
                ->setDesc('Selected Location has assets that cannot be removed.'),
        ];
    }
}
