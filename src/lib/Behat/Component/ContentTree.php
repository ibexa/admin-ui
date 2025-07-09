<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\BaseElementInterface;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;


class ContentTree extends Component
{
    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('header'))->assert()->textEquals('Content tree');
        $this->clearSearch();
        $this->getHTMLPage()->setTimeout(10)->find($this->getLocator('header'))->assert()->isVisible('Content tree');
    }
    public function verifyItemExists(string $itemPath): void
    {
        Assert::assertTrue($this->itemExists($itemPath));
    }

    public function itemExists(string $itemPath): bool
    {
        $this->clearSearch();


    }

    private function findNestedTreeElement(BaseElementInterface $baseElement, string $searchedElementName, int $indent): ElementInterface
    {
        return
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('header','.ibexa-content-tree-container .c-tb-header__name-content'),
            new VisibleCSSLocator('toggler','.c-tb-contextual-menu__toggler'),
            new VisibleCSSLocator('item', '.c-tb-list-item-single__element .c-tb-list-item-single__element--main')
            ];
    }
}