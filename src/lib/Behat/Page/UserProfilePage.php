<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\Behat\API\Facade\ContentFacade;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use PHPUnit\Framework\Assert;

class UserProfilePage extends Page
{
    private string $locationPath;

    private ContentFacade $contentFacade;

    public function __construct(Session $session, Router $router, ContentFacade $contentFacade)
    {
        parent::__construct($session, $router);
        $this->contentFacade = $contentFacade;
    }

    public function verifyIsLoaded(): void
    {
        Assert::assertEquals(
            'User profile',
            $this->getHTMLPage()->find($this->getLocator('pageTitle'))->getText()
        );
    }

    public function editSummary(): void
    {
        $this->getHTMLPage()->find($this->getLocator('editButton'))->click();
    }

    public function verifyUserProfileSummary(string $fullName, string $email, string $jobTitle, string $department, string $location): void
    {
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('fullName'))->assert()->textContains($fullName);
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('email'))->assert()->textEquals($email);
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('jobTitle'))->assert()->textEquals($jobTitle);
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('department'))->assert()->textEquals($department);
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('location'))->assert()->textEquals($location);
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-edit-header__title,.ibexa-page-title__content'),
            new VisibleCSSLocator('editButton', '.ibexa-user-profile-summary__header .ibexa-btn'),
            new VisibleCSSLocator('fullName', 'div.ibexa-details__item:nth-of-type(2) .ibexa-details__item-content'),
            new VisibleCSSLocator('email', 'div.ibexa-details__item:nth-of-type(3) .ibexa-details__item-content'),
            new VisibleCSSLocator('jobTitle', 'div.ibexa-details__item:nth-of-type(4) .ibexa-details__item-content'),
            new VisibleCSSLocator('department', 'div.ibexa-details__item:nth-of-type(5) .ibexa-details__item-content'),
            new VisibleCSSLocator('location', 'div.ibexa-details__item:nth-of-type(6) .ibexa-details__item-content'),
        ];
    }

    protected function getRoute(): string
    {
        return sprintf(
            '/user/profile/%d/view',
            $this->contentFacade->getLocationByLocationURL($this->locationPath)->getId()
        );
    }

    public function setExpectedUserData(string $locationPath): void
    {
        $this->locationPath = $locationPath;
    }

    public function getName(): string
    {
        return 'User profile';
    }
}
