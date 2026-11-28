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
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[CoversClass(ContentTab::class)]
final class ContentTabTest extends TestCase
{
    private const string MAIN_LANGUAGE = 'eng-GB';
    private const string OTHER_LANGUAGE = 'ger-DE';

    #[DataProvider('provideCurrentLanguage')]
    public function testCurrentLanguageCodeFollowsTheLanguageSwitcher(
        ?string $routeLanguageCode,
        string $expectedLanguageCode
    ): void {
        $requestStack = new RequestStack();
        if ($routeLanguageCode !== null) {
            $request = new Request();
            $request->attributes->set('languageCode', $routeLanguageCode);
            $requestStack->push($request);
        }

        $parameters = $this->createTab($requestStack)->getTemplateParameters($this->createContext());

        self::assertSame($expectedLanguageCode, $parameters['current_language_code']);
    }

    /**
     * @return iterable<string, array{?string, string}>
     */
    public static function provideCurrentLanguage(): iterable
    {
        yield 'main language from the route' => [self::MAIN_LANGUAGE, self::MAIN_LANGUAGE];
        yield 'other language from the route' => [self::OTHER_LANGUAGE, self::OTHER_LANGUAGE];
        yield 'no route language falls back to the language the content is loaded in' => [null, self::OTHER_LANGUAGE];
    }

    /**
     * @return array<string, mixed>
     */
    private function createContext(): array
    {
        $content = new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'id' => 42,
                    'mainLanguageCode' => self::MAIN_LANGUAGE,
                    'status' => ContentInfo::STATUS_PUBLISHED,
                ]),
                'languageCodes' => [self::MAIN_LANGUAGE, self::OTHER_LANGUAGE],
            ]),
            'prioritizedFieldLanguageCode' => self::OTHER_LANGUAGE,
        ]);

        return [
            'content' => $content,
            'contentType' => new ContentType([
                'identifier' => 'article',
                'fieldDefinitions' => new FieldDefinitionCollection([]),
            ]),
            'location' => self::createStub(Location::class),
        ];
    }

    private function createTab(RequestStack $requestStack): ContentTab
    {
        $configResolver = self::createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturnMap([
            ['languages', null, null, []],
        ]);

        $fieldsGroupsList = self::createStub(FieldsGroupsList::class);
        $fieldsGroupsList->method('getGroups')->willReturn([]);
        $fieldsGroupsList->method('getDefaultGroup')->willReturn('content');

        $languageService = self::createStub(LanguageService::class);
        $languageService->method('loadLanguages')->willReturn([]);

        return new ContentTab(
            self::createStub(Environment::class),
            self::createStub(TranslatorInterface::class),
            new FieldDefinitionGroupsUtil($fieldsGroupsList),
            $languageService,
            self::createStub(EventDispatcherInterface::class),
            $configResolver,
            $requestStack
        );
    }
}
