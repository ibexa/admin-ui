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
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\Repository;

class ObjectStateGroupPage extends Page
{
    /** @var string */
    protected $expectedObjectStateGroupName;

    /** @var \Ibexa\AdminUi\Behat\Component\Dialog */
    private $dialog;

    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    private $objectStates;

    /** @var \Ibexa\Contracts\Core\Repository\Repository */
    private $repository;

    /** @var mixed */
    private $expectedObjectStateGroupId;

    public function __construct(Session $session, Router $router, TableBuilder $tableBuilder, Dialog $dialog, Repository $repository)
    {
        parent::__construct($session, $router);
        $this->dialog = $dialog;
        $this->objectStates = $tableBuilder->newTable()->withParentLocator($this->getLocator('objectStatesTable'))->build();
        $this->repository = $repository;
    }

    public function editObjectState(string $itemName): void
    {
        $this->objectStates->getTableRow(['Object state name' => $itemName])->edit();
    }

    public function createObjectState(): void
    {
        $this->getHTMLPage()->find($this->getLocator('createButton'))->click();
    }

    public function setExpectedObjectStateGroupName(string $objectStateGroupName): void
    {
        $this->expectedObjectStateGroupName = $objectStateGroupName;

        /** @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup[] $objectStateGroups */
        $objectStateGroups = $this->repository->sudo(function () {
            return $this->repository->getObjectStateService()->loadObjectStateGroups();
        });

        foreach ($objectStateGroups as $objectStateGroup) {
            if ($objectStateGroup->getName() === $objectStateGroupName) {
                $this->expectedObjectStateGroupId = $objectStateGroup->id;
            }
        }
    }

    public function hasObjectStates(): bool
    {
        return count($this->objectStates->getColumnValues(['Object state name'])) > 0;
    }

    public function hasAttribute($label, $value): bool
    {
        return $this->getHTMLPage()
                    ->findAll($this->getLocator('objectStateGroupAttribute'))
                    ->getByCriterion(new ChildElementTextCriterion($this->getLocator('label'), $label))
                    ->find($this->getLocator('value'))
                    ->getText() === $value;
    }

    public function hasObjectState(string $objectStateName): bool
    {
        return $this->objectStates->hasElement(['Object state name' => $objectStateName]);
    }

    public function deleteObjectState(string $objectStateName)
    {
        $this->objectStates->getTableRow(['Object state name' => $objectStateName])->select();
        $this->getHTMLPage()->find($this->getLocator('deleteButton'))->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function edit()
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Edit'))
            ->click();
    }

    protected function getRoute(): string
    {
        return sprintf('/state/group/%d', $this->expectedObjectStateGroupId);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->setTimeout(3)
            ->waitUntilCondition(new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('objectStatesTable')))
            ->find($this->getLocator('pageTitle'))
            ->assert()->textEquals($this->expectedObjectStateGroupName);
    }

    public function getName(): string
    {
        return 'Object state group';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('objectStatesTable', '[name="object_states_delete"]'),
            new VisibleCSSLocator('createButton', '.ibexa-icon--create'),
            new VisibleCSSLocator('deleteButton', '.ibexa-icon--trash'),
            new VisibleCSSLocator('objectStateGroupAttribute', '.ibexa-details__item'),
            new VisibleCSSLocator('label', '.ibexa-label'),
            new VisibleCSSLocator('value', '.ibexa-details__item-content'),
            new VisibleCSSLocator('button', '.ibexa-btn'),
        ];
    }
}
