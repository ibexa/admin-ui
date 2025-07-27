<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Page\MyDraftsPage;
use PHPUnit\Framework\Assert;

final class MyDraftsContext implements Context
{
    private MyDraftsPage $myDraftsPage;

    public function __construct(MyDraftsPage $myDraftsPage)
    {
        $this->myDraftsPage = $myDraftsPage;
    }

    /**
     * @Given I delete the draft :draftName from my draft lists
     */
    public function iDeleteADraftFromTheList(string $draftName): void
    {
        $this->myDraftsPage->deleteDraft($draftName);
    }

    /**
     * @Given I see the draft :draftName is deleted
     */
    public function iSeeTheDraftIsDeleted(string $draftName): void
    {
        Assert::assertFalse($this->myDraftsPage->isDraftOnTheList($draftName));
    }

    /**
     * @Given I edit :draftName on MyDrafts page
     */
    public function iEditDraft(string $draftName): void
    {
        $this->myDraftsPage->edit($draftName);
    }
}
