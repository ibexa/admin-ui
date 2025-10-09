<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Component\ContentTree;
use Symfony\Component\Stopwatch\Stopwatch;

final class ContentTreeContext implements Context
{
    private ContentTree $contentTree;

    public function __construct(
        ContentTree $contentTree
    )
    {
        $this->contentTree = $contentTree;
    }

    /**
     * @Then I verify Content tree visibility
     */
    public function iAmOnContentTree(): void
    {
        $this->contentTree->verifyIsLoaded();
    }

    /**
     * @Then Content item :itemPath exists in Content tree
     */
    public function contentItemExistsInContentTree(string $itemPath): void
    {
        $this->contentTree->verifyIsLoaded();
        $this->contentTree->verifyItemExists($itemPath);
    }
    /**
     * @Given I wait :number seconds
     */
    public function iWait(string $number): void
    {
        $number = (int) $number;
        sleep($number);
    }
}
