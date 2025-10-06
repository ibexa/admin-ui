<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Couchbase\TimeoutException;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\BaseElementInterface;
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Exception\ElementNotFoundException;
use Ibexa\Behat\Browser\Locator\CSSLocator;
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

        try {
            $this->getHTMLPage()
                ->setTimeout(5)
                ->waitUntilCondition(new ElementExistsCondition($this->getHTMLPage(),
                    $this->getLocator('treeItem')));
        } catch (TimeoutException $e) {
            return false;
        }
        $searchedNode = $this->getHTMLPage()->find($itemPath);

        try {
            $this->searchForItem(end($itemPath));
        } catch (TimeoutException $e) {
            return false;
        }
        foreach ($pathParts as $indent => $itemPath) {
            try {
                $searchedNode = $this->findNestedTreeElement($searchedNode, $itemPath, $indent);
            } catch (ElementNotFoundException $e) {
                return false;
            } catch (TimeoutException $e) {
                return false;
            }

            if ($itemPath !== end($itemPath)) {
                $searchedNode = $searchedNode->find(new VisibleCSSLocator('', '.c-tb-list'));
            }
        }

        $this->getHTMLPage()
            ->setTimeout(5)
            ->waitUntilCondition(new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('treeItem')));

        return true;
    }
    private function findNestedTreeElement(BaseElementInterface $baseElement, string $searchedElementName, int $indent): ElementInterface
    {
        return $baseElement->findAll($this->getLocator('treeItem'))
            ->filter(static function (ElementInterface $element) use ($indent): bool {
                return $element->findAll(
                    new CSSLocator('', sprintf('[style*="--indent: %d;"]', $indent))
                )->any();
            })
            ->filter(static function (ElementInterface $element) use ($searchedElementName): bool {
                return str_replace(' ', '', $element->find(
                        new VisibleCSSLocator('', '.c-tb-list-item-single__element')
                    )->getText()) === $searchedElementName;
            })
            ->first();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('header', '.ibexa-content-tree-container .c-tb-header__name-content,.c-header .c-header__name'),
            new VisibleCSSLocator('treeItem', '.c-tb-list-item-single__label'),
            new VisibleCSSLocator('treeElement', '.ibexa-content-tree-container__root .c-tb-list-item-single__element'),
            ];
    }
}
