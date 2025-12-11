<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class UniqueURL extends Constraint
{
    public string $message = 'ez.url.unique';

    public function validatedBy(): string
    {
        return 'ezplatform.content_forms.validator.unique_url';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
