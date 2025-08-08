<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Page\ContentPreviewPage;

final readonly class ContentPreviewContext implements Context
{
    public function __construct(private ContentPreviewPage $contentPreviewPage)
    {
    }

    /**
     * @When I go to :viewName preview
     */
    public function iGoToPreview(string $viewName): void
    {
        $this->contentPreviewPage->verifyIsLoaded();
        $this->contentPreviewPage->goToView($viewName);
    }

    /**
     * @When I go back from content preview
     */
    public function iGoToBackFromPreview(): void
    {
        $this->contentPreviewPage->verifyIsLoaded();
        $this->contentPreviewPage->goBackToEditView();
    }
}
