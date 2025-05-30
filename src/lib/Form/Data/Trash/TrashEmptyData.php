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
class TrashEmptyData
{
    /**
     * @var bool
     */
    #[Assert\IsTrue]
    public $emptyTrash;

    /**
     * @param bool $emptyTrash
     */
    public function __construct(bool $emptyTrash = false)
    {
        $this->emptyTrash = $emptyTrash;
    }

    /**
     * @return bool
     */
    #[Assert\IsTrue]
    public function getEmptyTrash(): bool
    {
        return $this->emptyTrash;
    }

    /**
     * @param bool $emptyTrash
     */
    public function setEmptyTrash(bool $emptyTrash): void
    {
        $this->emptyTrash = $emptyTrash;
    }
}
