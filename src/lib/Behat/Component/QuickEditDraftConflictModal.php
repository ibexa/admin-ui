<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

/**
 * The confirmation modal admin.location.quick.field.edit.js opens when a quick-edit save finds
 * one or more existing drafts for the content item. It is a plain, unrouted `.ibexa-modal`
 * built and appended by the script itself (see buildDraftConflictModal()), distinct from the
 * full editor's own `#version-draft-conflict-modal` handled by {@see DraftConflictDialog}.
 */
final class QuickEditDraftConflictModal extends Component
{
    public function __construct(
        readonly Session $session
    ) {
        parent::__construct($session);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('modal'))->assert()->isVisible();
    }

    public function confirm(): void
    {
        $this->getHTMLPage()->find($this->getLocator('confirmButton'))->click();
    }

    public function dismiss(): void
    {
        $this->getHTMLPage()->find($this->getLocator('cancelButton'))->click();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('modal', '.ibexa-modal.show .modal-content'),
            new VisibleCSSLocator('confirmButton', '.ibexa-modal.show [data-quick-edit-action="confirm"]'),
            new VisibleCSSLocator('cancelButton', '.ibexa-modal.show [data-quick-edit-action="cancel"]'),
        ];
    }
}
