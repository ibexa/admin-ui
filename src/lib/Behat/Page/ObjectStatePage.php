<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use PHPUnit\Framework\Assert;

class ObjectStatePage extends Page
{
    private ?string $expectedObjectStateName = null;

    private Repository $repository;

    /** @var mixed */
    private $expectedObjectStateId;

    public function __construct(Session $session, Router $router, Repository $repository)
    {
        parent::__construct($session, $router);
        $this->repository = $repository;
    }

    public function hasAttribute($label, $value): bool
    {
        return $this->getHTMLPage()
                ->findAll($this->getLocator('objectStateAttribute'))
                ->getByCriterion(new ChildElementTextCriterion($this->getLocator('label'), $label))
                ->find($this->getLocator('value'))
                ->getText() === $value;
    }

    public function edit(): void
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Edit'))
            ->click();
    }

    public function getName(): string
    {
        return 'Object state';
    }

    public function setExpectedObjectStateName(string $objectStateName): void
    {
        $this->expectedObjectStateName = $objectStateName;
        $this->getHTMLPage()->setTimeout(3)->waitUntil(function () use ($objectStateName): bool {
            return $this->getObjectState($objectStateName) !== null;
        }, sprintf('Object state %s was not found', $objectStateName));

        $expectedObjectState = $this->getObjectState($objectStateName);
        $this->expectedObjectStateId = $expectedObjectState->id;
    }

    public function verifyIsLoaded(): void
    {
        Assert::assertEquals(
            sprintf('Object state: %s', $this->expectedObjectStateName),
            $this->getHTMLPage()->find($this->getLocator('pageTitle'))->getText()
        );
    }

    protected function getRoute(): string
    {
        return sprintf('/state/state/%s', $this->expectedObjectStateId);
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('propertiesTable', '.ibexa-container .ibexa-details'),
            new VisibleCSSLocator('objectStateAttribute', '.ibexa-details__item'),
            new VisibleCSSLocator('label', '.ibexa-label'),
            new VisibleCSSLocator('value', '.ibexa-details__item-content'),
            new VisibleCSSLocator('button', '.ibexa-btn'),
        ];
    }

    private function getObjectState(string $objectStateName): ?ObjectState
    {
        return $this->repository->sudo(static function (Repository $repository) use ($objectStateName) {
            foreach ($repository->getObjectStateService()->loadObjectStateGroups() as $group) {
                foreach ($repository->getObjectStateService()->loadObjectStates($group) as $objectState) {
                    if ($objectState->getName() === $objectStateName) {
                        return $objectState;
                    }
                }
            }

            return null;
        });
    }
}
