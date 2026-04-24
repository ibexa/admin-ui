<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Tab\LocationView\TranslationsTab;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UI\Dataset\TranslationsDataset;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\TwigComponents\ComponentInterface;
use Ibexa\TwigComponents\Component\Registry as ComponentRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class TranslationsTabTest extends TestCase
{
    private const string ROW_ACTIONS_GROUP = 'admin-ui-content-translations-row-actions';

    private DatasetFactory & MockObject $datasetFactory;

    private FormFactoryInterface & MockObject $formFactory;

    private PermissionResolver & MockObject $permissionResolver;

    private LanguageService & MockObject $languageService;

    private Content & MockObject $content;

    private Location & MockObject $location;

    private TranslationsDataset & MockObject $translationsDataset;

    protected function setUp(): void
    {
        $this->datasetFactory = $this->createMock(DatasetFactory::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->languageService = $this->createMock(LanguageService::class);
        $this->content = $this->createMock(Content::class);
        $this->location = $this->createMock(Location::class);
        $this->translationsDataset = $this->createMock(TranslationsDataset::class);
    }

    /**
     * @dataProvider provideHasTranslationActions
     */
    public function testGetTemplateParametersSetsHasTranslationActions(
        bool $hasComponents,
        bool $expectedFlag
    ): void {
        $this->configureContext();
        $tab = $this->createTab($hasComponents);

        $parameters = $tab->getTemplateParameters([
            'content' => $this->content,
            'location' => $this->location,
        ]);

        self::assertSame($expectedFlag, $parameters['has_translation_actions']);
    }

    /**
     * @return iterable<string, array{0: bool, 1: bool}>
     */
    public static function provideHasTranslationActions(): iterable
    {
        yield 'without components' => [
            false,
            false,
        ];

        yield 'with components' => [
            true,
            true,
        ];
    }

    private function configureContext(): void
    {
        $this->configureContentContext();
        $this->configureTranslationsDataset();
        $this->configureForms();
        $this->configurePermissions();
    }

    private function configureContentContext(): void
    {
        $contentInfo = $this->createMock(ContentInfo::class);
        $versionInfo = $this->createMock(VersionInfo::class);

        $this->content
            ->method('getVersionInfo')
            ->willReturn($versionInfo);
        $this->content
            ->method('getContentInfo')
            ->willReturn($contentInfo);

        $this->location
            ->method('getContentInfo')
            ->willReturn($contentInfo);

        $versionInfo
            ->method('getContentInfo')
            ->willReturn($contentInfo);
        $contentInfo
            ->method('getMainLanguageCode')
            ->willReturn('eng-GB');
    }

    private function configureTranslationsDataset(): void
    {
        $versionInfo = $this->content->getVersionInfo();

        $this->translationsDataset
            ->expects(self::once())
            ->method('load')
            ->with($versionInfo)
            ->willReturnSelf();
        $this->translationsDataset
            ->method('getTranslations')
            ->willReturn([]);
        $this->translationsDataset
            ->method('getLanguageCodes')
            ->willReturn([]);

        $this->datasetFactory
            ->expects(self::once())
            ->method('translations')
            ->willReturn($this->translationsDataset);
    }

    private function configureForms(): void
    {
        $this->formFactory
            ->expects(self::exactly(2))
            ->method('createNamed')
            ->willReturnOnConsecutiveCalls(
                $this->createFormMock(),
                $this->createFormMock()
            );
        $this->formFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->createFormMock());
    }

    private function configurePermissions(): void
    {
        $this->permissionResolver
            ->expects(self::once())
            ->method('canUser')
            ->willReturn(true);

        $this->languageService
            ->expects(self::once())
            ->method('loadLanguages')
            ->willReturn([]);
    }

    private function createTab(bool $hasComponents): TranslationsTab
    {
        $components = $hasComponents
            ? ['component-id' => $this->createStub(ComponentInterface::class)]
            : [];

        return new TranslationsTab(
            $this->createMock(Environment::class),
            $this->createMock(TranslatorInterface::class),
            $this->datasetFactory,
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->formFactory,
            $this->permissionResolver,
            $this->languageService,
            new ComponentRegistry([self::ROW_ACTIONS_GROUP => $components]),
        );
    }

    /**
     * @return FormInterface<mixed>&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createFormMock(): FormInterface
    {
        $form = $this->createMock(FormInterface::class);
        $form
            ->method('createView')
            ->willReturn(new FormView());

        return $form;
    }
}
