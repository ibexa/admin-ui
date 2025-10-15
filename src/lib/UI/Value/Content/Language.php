<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\Language as APILanguage;

/**
 * Extends original value object in order to provide additional fields.
 * Takes a standard language instance and retrieves properties from it in addition to the provided properties.
 */
class Language extends APILanguage
{
    protected bool $main;

    protected bool $userCanRemove;

    protected bool $userCanEdit = false;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly APILanguage $language,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($language) + $properties);
    }

    public function canDelete(): bool
    {
        return !$this->main && $this->userCanRemove;
    }

    public function canEdit(): bool
    {
        return $this->userCanEdit;
    }
}
