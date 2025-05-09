<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Dialog;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\ContentTypeService;

class ContentTypeGroupPage extends Page
{
    /** @var \Ibexa\AdminUi\Behat\Component\AdminList */
    protected $adminList;

    /** @var string */
    protected $expectedName;

    private ContentTypeService $contentTypeService;

    /** @var mixed */
    private $contentTypeGroupId;

    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    private TableInterface $table;

    private Dialog $dialog;

    public function __construct(Session $session, Router $router, ContentTypeService $contentTypeService, TableBuilder $tableBuilder, Dialog $dialog)
    {
        parent::__construct($session, $router);
        $this->contentTypeService = $contentTypeService;
        $this->table = $tableBuilder->newTable()->withParentLocator($this->getLocator('tableContainer'))->build();
        $this->dialog = $dialog;
    }

    public function hasContentTypes(): bool
    {
        return $this->table->isEmpty() === false;
    }

    public function edit(string $contentTypeName): void
    {
        $this->getHTMLPage()->find($this->getLocator('scrollableContainer'))->scrollToBottom($this->getSession());
        $this->table->getTableRow(['Name' => $contentTypeName])->edit();
    }

    public function goTo(string $contentTypeName): void
    {
        $this->table->getTableRow(['Name' => $contentTypeName])->goToItem();
    }

    public function createNew(): void
    {
        $this->getHTMLPage()->find($this->getLocator('createButton'))->click();
    }

    public function isContentTypeOnTheList($contentTypeName): bool
    {
        return $this->table->hasElement(['Name' => $contentTypeName]);
    }

    public function delete(string $contentTypeName): void
    {
        $contentTypeLabelLocator = $this->getLocator('contentTypeLabel');
        $listElement = $this->getHTMLPage()
            ->findAll($contentTypeLabelLocator)
            ->getByCriterion(new ElementTextCriterion($contentTypeName));
        usleep(1000000); //TODO : refactor after redesign
        $listElement->mouseOver();
        $this->table->getTableRow(['Name' => $contentTypeName])->select();
        $this->getHTMLPage()->find($this->getLocator('deleteButton'))->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function hasAssignedContentItems(string $contentTypeGroupName): bool
    {
        return $this->table->getTableRow(['Name' => $contentTypeGroupName])->getCellValue('Number of content types') > 0;
    }

    protected function getRoute(): string
    {
        return sprintf('/contenttypegroup/%d', $this->contentTypeGroupId);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->find($this->getLocator('pageTitle'))
            ->assert()->textEquals($this->expectedName);
        $this->getHTMLPage()
            ->find($this->getLocator('listHeader'))
            ->assert()->textContains('List');
    }

    public function setExpectedContentTypeGroupName(string $expectedName): void
    {
        $this->expectedName = $expectedName;
        $groups = $this->contentTypeService->loadContentTypeGroups();

        foreach ($groups as $group) {
            if ($group->identifier === $expectedName) {
                $this->contentTypeGroupId = $group->id;

                return;
            }
        }
    }

    public function getName(): string
    {
        return 'Content type group';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('createButton', '.ibexa-icon--create'),
            new VisibleCSSLocator('listHeader', '.ibexa-table-header .ibexa-table-header__headline, header .ibexa-table__headline, header h5'),
            new VisibleCSSLocator('tableContainer', '.ibexa-container'),
            new VisibleCSSLocator('deleteButton', '.ibexa-icon--trash,button[data-original-title^="Delete"]'),
            new VisibleCSSLocator('tableItem', '.ibexa-main-container tbody tr'),
            new VisibleCSSLocator('contentTypeLabel', '.ibexa-table__cell > a'),
            new VisibleCSSLocator('scrollableContainer', '.ibexa-back-to-top-scroll-container'),
        ];
    }
}
