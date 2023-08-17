<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\TableNavigationTab;
use Ibexa\Behat\Browser\Locator\LocatorInterface;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;

class SystemInfoPage extends Page
{
    /** @var \Ibexa\AdminUi\Behat\Component\TableNavigationTab */
    protected $tableNavigationTab;

    /** @var \Ibexa\AdminUi\Behat\Component\Table\TableInterface */
    private $table;

    public function __construct(Session $session, Router $router, TableNavigationTab $tableNavigationTab, TableBuilder $tableBuilder)
    {
        parent::__construct($session, $router);

        $this->tableNavigationTab = $tableNavigationTab;
        $this->table = $tableBuilder
            ->newTable()
            ->withParentLocator($this->getLocator('packagesTable'))
            ->build()
        ;
    }

    public function goToTab(string $tabName)
    {
        $this->tableNavigationTab->goToTab($tabName);
    }

    public function verifyCurrentTableHeader(string $header)
    {
        $this->getHTMLPage()->find($this->getHeaderLocator($header))->assert()->textEquals($header);
    }

    public function verifyPackages(array $packages)
    {
        $actualPackageData = $this->table->getColumnValues(['Name']);
        $names = array_column($actualPackageData, 'Name');

        foreach ($packages as $package) {
            Assert::assertContains($package, $names);
        }
    }

    public function verifyBundles(array $bundleNames)
    {
        $this->verifyPackages($bundleNames);
    }

    public function verifyIsLoaded(): void
    {
        $this->tableNavigationTab->verifyIsLoaded();
        $this->verifyCurrentTableHeader('Product');
    }

    public function getName(): string
    {
        return 'System Information';
    }

    protected function getRoute(): string
    {
        return '/systeminfo';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('packagesTable', '.tab-pane.active div.ibexa-fieldgroup:nth-of-type(2)'),
        ];
    }

    private function getHeaderLocator(string $header): LocatorInterface
    {
        $normalHeader = new VisibleCSSLocator('normalHeader', '.ibexa-fieldgroup__name');
        $boldedHeader = new VisibleCSSLocator('boldedHeader', '.ibexa-table-header__headline');

        switch ($header) {
            case 'Repository':
            case 'Hardware':
            case 'PHP':
            case 'Services':
                return $normalHeader;
            case 'Product':
            case 'Composer':
            case 'Symfony Kernel':
                return $boldedHeader;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported header: %s', $header));
        }
    }
}
