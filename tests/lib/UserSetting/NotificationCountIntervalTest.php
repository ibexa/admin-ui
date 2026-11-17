<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UserSetting;

use Ibexa\AdminUi\UserSetting\NotificationCountInterval;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;

final class NotificationCountIntervalTest extends TestCase
{
    private const string TRANSLATIONS = __DIR__ . '/../../../src/bundle/Resources/translations/ibexa_user_settings.en.xliff';

    public function testFormOffersOnlyThePresetsWithLabelsTranslatedOnRendering(): void
    {
        $setting = $this->createSetting(30000);
        $translator = $this->createTranslator();

        $form = $setting->mapFieldForm($this->createFormFactory()->createBuilder(), $setting)->getForm();
        $labels = [];
        foreach ($form->createView()->vars['choices'] as $choice) {
            self::assertInstanceOf(TranslatableMessage::class, $choice->label);
            $labels[$choice->value] = $choice->label->trans($translator);
        }

        self::assertSame(
            [
                '30000' => '30 seconds',
                '60000' => '1 minute',
                '300000' => '5 minutes',
                '900000' => '15 minutes',
                '1800000' => '30 minutes',
            ],
            $labels
        );
    }

    public function testFormRejectsAValueThatIsNotAPreset(): void
    {
        $setting = $this->createSetting(30000);
        $formBuilder = $this->createFormFactory()->createBuilder();
        $form = $formBuilder->add($setting->mapFieldForm($formBuilder, $setting))->getForm();

        $form->submit(['value' => '45000']);

        self::assertFalse($form->isValid());
    }

    #[DataProvider('provideDisplayValues')]
    public function testDisplayValue(
        string $storageValue,
        string $expectedDisplayValue
    ): void {
        self::assertSame($expectedDisplayValue, $this->createSetting(30000)->getDisplayValue($storageValue));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideDisplayValues(): iterable
    {
        yield 'seconds' => ['30000', '30 seconds'];
        yield 'one minute' => ['60000', '1 minute'];
        yield 'minutes' => ['300000', '5 minutes'];
        yield 'not a preset shows the configured interval' => ['120000', '30 seconds'];
        yield 'not a number shows the configured interval' => ['abc', '30 seconds'];
    }

    #[DataProvider('provideStoredIntervals')]
    public function testResolveInterval(
        string $storageValue,
        int $expectedInterval
    ): void {
        self::assertSame($expectedInterval, $this->createSetting(45000)->resolveInterval($storageValue));
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function provideStoredIntervals(): iterable
    {
        yield 'stored preset' => ['300000', 300000];
        yield 'not a preset falls back to the configured interval' => ['120000', 45000];
        yield 'zero falls back to the configured interval' => ['0', 45000];
        yield 'not a number falls back to the configured interval' => ['abc', 45000];
        yield 'negative number falls back to the configured interval' => ['-60000', 45000];
        yield 'empty value falls back to the configured interval' => ['', 45000];
    }

    private function createSetting(int $configuredInterval): NotificationCountInterval
    {
        $configResolver = self::createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturnMap([
            ['notification_count.interval', null, null, $configuredInterval],
        ]);

        return new NotificationCountInterval($this->createTranslator(), $configResolver);
    }

    private function createFormFactory(): FormFactoryInterface
    {
        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();
    }

    private function createTranslator(): Translator
    {
        $translator = new Translator('en');
        $translator->addLoader('xliff', new XliffFileLoader());
        $translator->addResource('xliff', self::TRANSLATIONS, 'en', 'ibexa_user_settings');

        return $translator;
    }
}
