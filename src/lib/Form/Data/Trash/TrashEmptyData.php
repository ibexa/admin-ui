<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Trash;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo add validation
 */
final class TrashEmptyData
{
    public function __construct(#[Assert\IsTrue] public bool $emptyTrash = false)
    {
    }

    public function setEmptyTrash(bool $emptyTrash): void
    {
        $this->emptyTrash = $emptyTrash;
    }

    #[Assert\IsTrue]
    public function getEmptyTrash(): bool
    {
        return $this->emptyTrash;
    }
}
