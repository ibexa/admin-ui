<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Ibexa\AdminUi\Behat\BrowserContext\AdminUpdateContext;
use Ibexa\AdminUi\Behat\BrowserContext\BookmarkContext;
use Ibexa\AdminUi\Behat\BrowserContext\ContentActionsMenuContext;
use Ibexa\AdminUi\Behat\BrowserContext\ContentPreviewContext;
use Ibexa\AdminUi\Behat\BrowserContext\ContentTreeContext;
use Ibexa\AdminUi\Behat\BrowserContext\ContentTypeContext;
use Ibexa\AdminUi\Behat\BrowserContext\ContentUpdateContext;
use Ibexa\AdminUi\Behat\BrowserContext\ContentViewContext;
use Ibexa\AdminUi\Behat\BrowserContext\DashboardContext;
use Ibexa\AdminUi\Behat\BrowserContext\LanguageContext;
use Ibexa\AdminUi\Behat\BrowserContext\MyDraftsContext;
use Ibexa\AdminUi\Behat\BrowserContext\NavigationContext;
use Ibexa\AdminUi\Behat\BrowserContext\NotificationContext;
use Ibexa\AdminUi\Behat\BrowserContext\ObjectStatesContext;
use Ibexa\AdminUi\Behat\BrowserContext\RolesContext;
use Ibexa\AdminUi\Behat\BrowserContext\SearchContext;
use Ibexa\AdminUi\Behat\BrowserContext\SectionsContext;
use Ibexa\AdminUi\Behat\BrowserContext\SystemInfoContext;
use Ibexa\AdminUi\Behat\BrowserContext\TrashContext;
use Ibexa\AdminUi\Behat\BrowserContext\UDWContext;
use Ibexa\AdminUi\Behat\BrowserContext\UserPreferencesContext;
use Ibexa\AdminUi\Behat\BrowserContext\UserProfileContext;
use Ibexa\Behat\API\Context\ContentContext;
use Ibexa\Behat\API\Context\ContentTypeContext as ApiContentTypeContext;
use Ibexa\Behat\API\Context\RoleContext;
use Ibexa\Behat\API\Context\TestContext;
use Ibexa\Behat\API\Context\TrashContext as ApiTrashContext;
use Ibexa\Behat\API\Context\UserContext;
use Ibexa\Behat\Browser\Context\AuthenticationContext;
use Ibexa\Behat\Browser\Context\DebuggingContext;
use Ibexa\User\Behat\Context\UserSettingsContext;

return (new Config())
    ->withProfile((new Profile('browser'))
        ->withExtension(new Extension(MinkExtension::class, [
            'files_path' => '%paths.base%/vendor/ibexa/behat/src/lib/Behat/TestFiles/',
        ]))
        ->withSuite((new Suite('admin-ui'))
            ->withContexts(
                ApiContentTypeContext::class,
                ContentContext::class,
                RoleContext::class,
                TestContext::class,
                ApiTrashContext::class,
                UserContext::class,
                DebuggingContext::class,
                AuthenticationContext::class,
                NavigationContext::class,
                ContentActionsMenuContext::class,
                UDWContext::class,
                ContentViewContext::class,
                AdminUpdateContext::class,
                BookmarkContext::class,
                ContentPreviewContext::class,
                ContentTreeContext::class,
                ContentTypeContext::class,
                ContentUpdateContext::class,
                ContentViewContext::class,
                DashboardContext::class,
                LanguageContext::class,
                MyDraftsContext::class,
                NavigationContext::class,
                NotificationContext::class,
                ObjectStatesContext::class,
                RolesContext::class,
                SearchContext::class,
                SectionsContext::class,
                SystemInfoContext::class,
                TrashContext::class,
                UDWContext::class,
                UserPreferencesContext::class,
                AuthenticationContext::class,
                DebuggingContext::class,
                UserSettingsContext::class,
                UserProfileContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/admin-ui/features/standard')
            ->withFilter(new TagFilter('~@broken')))
        ->withSuite((new Suite('personas'))
            ->withContexts(
                DebuggingContext::class,
                AuthenticationContext::class,
                NavigationContext::class,
                ContentViewContext::class,
                ContentUpdateContext::class,
                ContentPreviewContext::class,
                ContentActionsMenuContext::class,
                NotificationContext::class,
                TrashContext::class,
                UDWContext::class,
                UserPreferencesContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/admin-ui/features/personas'))
        ->withSuite((new Suite('admin-ui-full'))
            ->withContexts(
                ContentContext::class,
                ApiContentTypeContext::class,
                RoleContext::class,
                TestContext::class,
                ApiTrashContext::class,
                UserContext::class,
                AdminUpdateContext::class,
                ContentActionsMenuContext::class,
                BookmarkContext::class,
                ContentPreviewContext::class,
                ContentTreeContext::class,
                ContentTypeContext::class,
                ContentUpdateContext::class,
                ContentViewContext::class,
                DashboardContext::class,
                LanguageContext::class,
                MyDraftsContext::class,
                NavigationContext::class,
                NotificationContext::class,
                ObjectStatesContext::class,
                RolesContext::class,
                SearchContext::class,
                SectionsContext::class,
                SystemInfoContext::class,
                TrashContext::class,
                UDWContext::class,
                UserPreferencesContext::class,
                AuthenticationContext::class,
                DebuggingContext::class,
                UserSettingsContext::class,
                UserProfileContext::class
            )
            ->withPaths('%paths.base%/vendor/ibexa/admin-ui/features/')));
