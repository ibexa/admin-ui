<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig\Components\Table;

final class Column
{
    /**
     * @param callable(mixed): string $renderer
     */
    public function __construct(
        public string $identifier,
        public string $label,
        public $renderer,
        public int $priority = 0,
    ) {
    }
}
