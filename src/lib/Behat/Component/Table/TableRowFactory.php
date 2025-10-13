<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Table;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Locator\LocatorCollection;

final readonly class TableRowFactory
{
    public function __construct(private Session $session)
    {
    }

    public function createRow(
        ElementInterface $element,
        LocatorCollection $locatorCollection
    ): TableRow {
        return new TableRow(
            $this->session,
            $element,
            $locatorCollection
        );
    }
}
