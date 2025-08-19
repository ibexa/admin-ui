<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;

use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class ContentTree extends Component
{
    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('header'))->assert()->textEquals('Content tree');
//        $this->getHTMLPage()->setTimeout(10)->find($this->getLocator('item'))->assert()->isVisible();

    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('header','.ibexa-content-tree-container .c-tb-header__name-content,.c-header .c-header__name'),
//            new VisibleCSSLocator('optionsButton', '.c-tb-contextual-menu__toggler'),
//            new VisibleCSSLocator('menuOption', '.c-tb-action-list__item'),
//            new VisibleCSSLocator('item', '.c-tb-list-item-single__element .c-tb-list-item-single__element--main')
        ];
}

}