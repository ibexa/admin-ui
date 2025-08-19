<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Component\ContentTree;

final class ContentTreeContext implements Context
{
    private ContentTree $contentTree;

    public function __construct (
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

}
