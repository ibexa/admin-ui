<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Tab\LocationView\ContentTab;
use Ibexa\AdminUi\Util\FieldDefinitionGroupsUtil;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class ContentTabTest extends TestCase
{
    private const array SUPPORTED_FIELD_TYPES = ['ibexa_string', 'ibexa_text', 'ibexa_integer'];

    private const array EXCLUDED_FIELD_TYPES = ['ibexa_integer'];

    private FieldDefinitionGroupsUtil $fieldDefinitionGroupsUtil;

    private LanguageService & MockObject $languageService;

    private ConfigResolverInterface & MockObject $configResolver;

    private PermissionResolver & MockObject $permissionResolver;

    private RequestStack $requestStack;

    private Content & MockObject $content;

    private Location & MockObject $location;

    private ContentType & MockObject $contentType;

    protected function setUp(): void
    {
        $fieldsGroupsListHelper = $this->createMock(FieldsGroupsList::class);
        $fieldsGroupsListHelper->method('getGroups')->willReturn([]);
        $fieldsGroupsListHelper->method('getDefaultGroup')->willReturn('content');
        $this->fieldDefinitionGroupsUtil = new FieldDefinitionGroupsUtil($fieldsGroupsListHelper);
        $this->languageService = $this->createMock(LanguageService::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->requestStack = new RequestStack();
        $this->content = $this->createMock(Content::class);
        $this->location = $this->createMock(Location::class);
        $this->contentType = $this->createMock(ContentType::class);

        $versionInfo = $this->createMock(VersionInfo::class);
        $versionInfo->method('getLanguageCodes')->willReturn([]);
        $this->content->method('getVersionInfo')->willReturn($versionInfo);

        $this->contentType->method('getFieldDefinitions')->willReturn(new FieldDefinitionCollection());
        $this->languageService->method('loadLanguages')->willReturn([]);

        $this->configResolver
            ->method('getParameter')
            ->willReturnMap([
                ['languages', null, null, []],
                ['inline_field_edit.enabled', null, null, true],
                ['inline_field_edit.supported_field_types', null, null, self::SUPPORTED_FIELD_TYPES],
                ['inline_field_edit.excluded_field_types', null, null, self::EXCLUDED_FIELD_TYPES],
            ]);
    }

    public function testCanEditReflectsContentEditPermission(): void
    {
        $this->permissionResolver
            ->expects(self::once())
            ->method('canUser')
            ->with('content', 'edit', $this->content)
            ->willReturn(true);

        $parameters = $this->createTab()->getTemplateParameters([
            'content' => $this->content,
            'contentType' => $this->contentType,
            'location' => $this->location,
        ]);

        self::assertTrue($parameters['can_edit']);
    }

    public function testInlineFieldEditConfigIsEffectiveSupportedMinusExcluded(): void
    {
        $this->permissionResolver->method('canUser')->willReturn(false);

        $parameters = $this->createTab()->getTemplateParameters([
            'content' => $this->content,
            'contentType' => $this->contentType,
            'location' => $this->location,
        ]);

        self::assertSame(
            [
                'enabled' => true,
                'field_types' => ['ibexa_string', 'ibexa_text'],
            ],
            $parameters['inline_field_edit']
        );
    }

    public function testCurrentLanguageCodeComesFromRouteAttributeWhenPresent(): void
    {
        $this->permissionResolver->method('canUser')->willReturn(false);
        $this->content->method('getDefaultLanguageCode')->willReturn('eng-GB');

        // The language switcher navigates to the 'ibexa.content.translation.view' route,
        // whose 'languageCode' placeholder the router resolves into request attributes,
        // never into the query string.
        $request = new Request();
        $request->attributes->set('languageCode', 'ger-DE');
        $this->requestStack->push($request);

        $parameters = $this->createTab()->getTemplateParameters([
            'content' => $this->content,
            'contentType' => $this->contentType,
            'location' => $this->location,
        ]);

        self::assertSame('ger-DE', $parameters['current_language_code']);
    }

    public function testCurrentLanguageCodeFallsBackToContentDefaultLanguageCode(): void
    {
        $this->permissionResolver->method('canUser')->willReturn(false);
        $this->content->method('getDefaultLanguageCode')->willReturn('eng-GB');

        $parameters = $this->createTab()->getTemplateParameters([
            'content' => $this->content,
            'contentType' => $this->contentType,
            'location' => $this->location,
        ]);

        self::assertSame('eng-GB', $parameters['current_language_code']);
    }

    private function createTab(): ContentTab
    {
        return new ContentTab(
            $this->createMock(Environment::class),
            $this->createMock(TranslatorInterface::class),
            $this->fieldDefinitionGroupsUtil,
            $this->languageService,
            $this->createMock(EventDispatcherInterface::class),
            $this->configResolver,
            $this->permissionResolver,
            $this->requestStack
        );
    }
}
