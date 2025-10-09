<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use PHPUnit\Framework\Assert;

class ContentTree extends Component
{
    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('header'))->assert()->textEquals('Content tree');
    }
    public function verifyItemExists(string $itemPath): void
    {
        Assert::assertTrue($this->itemExists($itemPath));
    }
    private function itemExists(string $itemPath): bool
    {

        $pathParts = explode('/', $itemPath);
        $searchedElement = $this->getHTMLPage()->findAll($this->getLocator('contextInTree'))->getByCriterion(new ElementTextCriterion(end($pathParts)));
        return $searchedElement !== null;
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('header', '.ibexa-content-tree-container .c-tb-header__name-content,.c-header .c-header__name'),
            new VisibleCSSLocator('treeItem', '.c-tb-list-item-single__label'),
            new VisibleCSSLocator('treeElement', '.ibexa-content-tree-container__root .c-tb-list-item-single__element'),
            new VisibleCSSLocator('search', '.c-tb-search .ibexa-input'),
            new VisibleCSSLocator('contextInTree', '.c-tb-list-item-single__link'),
        ];
    }
}
