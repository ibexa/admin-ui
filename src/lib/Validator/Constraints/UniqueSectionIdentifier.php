<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class UniqueSectionIdentifier extends Constraint
{
    /** %identifier% placeholder is passed. */
    public string $message = 'ez.section.identifier.unique';

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

    public function validatedBy(): string
    {
        return 'ezplatform.content_forms.validator.unique_section_identifier';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
