<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\View\Template;

use Pagerfanta\View\Template\TwitterBootstrap4Template;

/**
 * Template to customize Pagerfanta pagination.
 */
class EzPagerfantaTemplate extends TwitterBootstrap4Template
{
    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct();

        $this->setOptions([
            'prev_message' => '',
            'next_message' => '',
            'active_suffix' => '',
            'css_container_class' => 'pagination ibexa-pagination__navigation',
        ]);
    }
}
