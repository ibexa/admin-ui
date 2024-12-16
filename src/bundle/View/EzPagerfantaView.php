<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\View;

use Ibexa\Bundle\AdminUi\View\Template\EzPagerfantaTemplate;
use Pagerfanta\View\DefaultView;
use Pagerfanta\View\Template\TemplateInterface;

/**
 * View to render Pagerfanta pagination.
 */
class EzPagerfantaView extends DefaultView
{
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new EzPagerfantaTemplate();
    }

    protected function getDefaultProximity(): int
    {
        return 3;
    }

    public function getName(): string
    {
        return 'ibexa';
    }
}
