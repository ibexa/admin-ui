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
use Ibexa\AdminUi\Behat\Component\TableNavigationTab;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\Repository;
use PHPUnit\Framework\Assert;

class RolePage extends Page
{
    public Dialog $dialog;

    private ?string $expectedRoleName = null;

    private TableNavigationTab $tableNavigationTab;

    private Repository $repository;

    private int $expectedRoleId;

    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    private TableInterface $policies;

    /** @var \Ibexa\AdminUi\Behat\Component\Table\Table */
    private TableInterface $assignments;

    public function __construct(
        Session $session,
        Router $router,
        TableNavigationTab $tableNavigationTab,
        Dialog $dialog,
        Repository $repository,
        TableBuilder $tableBuilder
    ) {
        parent::__construct($session, $router);
        $this->tableNavigationTab = $tableNavigationTab;
        $this->dialog = $dialog;
        $this->repository = $repository;
        $this->policies = $tableBuilder->newTable()->withParentLocator($this->getLocator('policiesTable'))->build();
        $this->assignments = $tableBuilder->newTable()->withParentLocator($this->getLocator('assignmentTable'))->build();
    }

    /**
     * Verifies if Role with Limitation from given list is present.
     *
     * @param string $listName
     * @param string $moduleAndFunction
     * @param string $limitation
     *
     * @return bool
     */
    public function isRoleWithLimitationPresent(string $moduleAndFunction, string $limitation): bool
    {
        $this->tableNavigationTab->goToTab('Policies');
        $actualPoliciesList = $this->policies->getColumnValues(['Module', 'Function', 'Limitations']);

        [$expectedModule, $expectedFunction] = explode('/', $moduleAndFunction);

        foreach ($actualPoliciesList as $policy) {
            if (
                $policy['Module'] === $expectedModule &&
                $policy['Function'] === $expectedFunction &&
                $this->isLimitationCorrect($limitation, $policy['Limitations'])
            ) {
                return true;
            }
        }

        return false;
    }

    private function isLimitationCorrect(string $expectedLimitation, string $actualLimitations): bool
    {
        if ($expectedLimitation === 'None') {
            return $actualLimitations === 'None';
        }

        [$expectedLimitationType, $expectedLimitationValue] = explode(':', $expectedLimitation);
        $expectedLimitationValues = array_map(static function (string $value): string {
            return trim($value);
        }, explode(',', $expectedLimitationValue));

        $limitationTypePos = strpos($actualLimitations, $expectedLimitationType);
        $actualLimitationsStartingFromExpectedType = substr($actualLimitations, $limitationTypePos);

        $valuePositionsDictionary = [];

        foreach ($expectedLimitationValues as $value) {
            $position = strpos($actualLimitationsStartingFromExpectedType, $value);
            if ($position === false) {
                return false;
            }

            $valuePositionsDictionary[$position] = $value;
        }

        ksort($valuePositionsDictionary);
        $combinedExpectedLimitation = sprintf('%s: %s', $expectedLimitationType, implode(', ', $valuePositionsDictionary));

        return strpos($actualLimitations, $combinedExpectedLimitation) !== false;
    }

    public function setExpectedRoleName(string $roleName): void
    {
        $this->expectedRoleName = $roleName;

        /** @var \Ibexa\Contracts\Core\Repository\Values\User\Role[] $roles */
        $roles = $this->repository->sudo(static function (Repository $repository): iterable {
            return $repository->getRoleService()->loadRoles();
        });

        foreach ($roles as $role) {
            if ($role->identifier === $roleName) {
                $this->expectedRoleId = $role->id;
                break;
            }
        }
    }

    public function goToTab(string $tabName): void
    {
        $this->tableNavigationTab->goToTab($tabName);
    }

    public function getRoute(): string
    {
        return sprintf('/role/%d', $this->expectedRoleId);
    }

    public function getName(): string
    {
        return 'Role';
    }

    public function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('button', '.ibexa-btn'),
            new VisibleCSSLocator('deleteAssignmentButton', '#delete-role-assignments'),
            new VisibleCSSLocator('deletePoliciesButton', '#delete-policies'),
            new VisibleCSSLocator('assignmentTable', '[name="role_assignments_delete"]'),
            new VisibleCSSLocator('policiesTable', '[name="policies_delete"]'),
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
        ];
    }

    public function verifyIsLoaded(): void
    {
        $this->tableNavigationTab->verifyIsLoaded();
        $this->getHTMLPage()
            ->find($this->getLocator('pageTitle'))
            ->assert()->textEquals(sprintf('Role "%s"', $this->expectedRoleName));
    }

    public function hasPolicies(): bool
    {
        $this->tableNavigationTab->goToTab('Policies');

        return count($this->policies->getColumnValues(['Module'])) > 0;
    }

    public function hasAssignments(): bool
    {
        $this->tableNavigationTab->goToTab('Assignments');

        return count($this->assignments->getColumnValues(['User/Group'])) > 0;
    }

    public function verifyAssignments(array $expectedAssignments): void
    {
        $this->goToTab('Assignment');

        $actualAssignments = $this->assignments->getColumnValues(['User/Group', 'Limitation']);

        foreach ($expectedAssignments as $expectedAssignment) {
            Assert::assertContains($expectedAssignment, $actualAssignments);
        }

        Assert::assertCount(count($expectedAssignments), $actualAssignments);
    }

    public function startAssigningUsers(): void
    {
        $this->goToTab('Assignments');
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Assign to Users/Groups'))
            ->click();
    }

    public function deleteAssignments(array $itemNames): void
    {
        $this->goToTab('Assignments');

        foreach ($itemNames as $item) {
            $this->assignments->getTableRow(['User/Group' => $item])->select();
        }

        $this->getHTMLPage()
            ->find($this->getLocator('deleteAssignmentButton'))
            ->click();

        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function deletePolicies(array $itemNames): void
    {
        $this->goToTab('Policies');

        foreach ($itemNames as $item) {
            $this->policies->getTableRow(['Module' => $item])->select();
        }

        $this->getHTMLPage()
            ->find($this->getLocator('deletePoliciesButton'))
            ->click();

        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function createPolicy(): void
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Add'))
            ->click();
    }

    public function editPolicy(string $moduleName, string $functionName): void
    {
        $this->policies->getTableRow(['Module' => $moduleName, 'Function' => $functionName])->edit();
    }
}
