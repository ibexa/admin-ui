<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Tab;

/**
 * Tab interface representing UI tabs. Tabs are assigned to groups which are rendered in the UI.
 * Use `ibexa.admin_ui.tab` tag with attribute `group` to tag your concrete implementation service.
 */
interface TabInterface
{
    /**
     * Returns identifier of the tab.
     */
    public function getIdentifier(): string;

    /**
     * Returns name of the tab which is displayed as a tab's title in the UI.
     */
    public function getName(): string;

    /**
     * Returns HTML body of the tab.
     *
     * @param array<string, mixed> $parameters
     */
    public function renderView(array $parameters): string;
}
