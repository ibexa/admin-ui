<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Translation\Extractor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class TypeScriptTranslationExtractorScriptTest extends TestCase
{
    /**
     * @dataProvider provideFixtures
     *
     * @param array<int, array{id: string, domain: ?string, desc: ?string}> $expectedMessages
     * @param array<int, string> $expectedWarningSubstrings
     */
    public function testExtractsExpectedMessages(
        string $extension,
        string $source,
        array $expectedMessages,
        array $expectedWarningSubstrings = [],
    ): void {
        $file = tempnam(sys_get_temp_dir(), 'ts_extractor_');
        rename($file, $file .= '.' . $extension);
        file_put_contents($file, $source);

        $process = new Process(['node', $this->getScriptPath(), $file]);
        $process->run();

        self::assertTrue($process->isSuccessful(), $process->getErrorOutput());

        $extractionResponse = json_decode($process->getOutput(), true);

        self::assertSame($expectedMessages, $extractionResponse['messages']);
        self::assertCount($expectedWarningSubstrings === [] ? 0 : count($expectedWarningSubstrings), $extractionResponse['warnings']);

        foreach ($expectedWarningSubstrings as $index => $substring) {
            self::assertStringContainsString($substring, $extractionResponse['warnings'][$index]);
        }

        unlink($file);
    }

    /**
     * @return iterable<string, array{
     *     0: string,
     *     1: string,
     *     2: array<int, array{id: string, domain: ?string, desc: ?string}>,
     *     3?: array<int, string>
     * }>
     */
    public static function provideFixtures(): iterable
    {
        yield 'trans with domain and desc' => [
            'ts',
            <<<'TS'
            Translator.trans(
                /* @Desc("Save button label") */ 'admin_ui.save',
                {},
                'ibexa_admin_ui'
            );
            TS,
            [['id' => 'admin_ui.save', 'domain' => 'ibexa_admin_ui', 'desc' => 'Save button label']],
        ];

        yield 'transChoice with domain at index 3' => [
            'ts',
            "Translator.transChoice('admin_ui.items', count, {}, 'ibexa_admin_ui');",
            [['id' => 'admin_ui.items', 'domain' => 'ibexa_admin_ui', 'desc' => null]],
        ];

        yield 'trans without domain falls back to null' => [
            'ts',
            "Translator.trans('admin_ui.no_domain');",
            [['id' => 'admin_ui.no_domain', 'domain' => null, 'desc' => null]],
        ];

        yield 'unrelated call is ignored' => [
            'ts',
            "SomeOtherObject.trans('not.extracted');",
            [],
        ];

        yield 'call inside JSX in a tsx file' => [
            'tsx',
            <<<'TSX'
            const Label = () => (
                <span>{Translator.trans('admin_ui.jsx_label', {}, 'ibexa_admin_ui')}</span>
            );
            TSX,
            [['id' => 'admin_ui.jsx_label', 'domain' => 'ibexa_admin_ui', 'desc' => null]],
        ];

        yield 'typed function with generics does not break parsing' => [
            'tsx',
            <<<'TSX'
            interface Props {
                count: number;
            }

            const format = <T,>(value: T): string => String(value);

            export const Component = ({ count }: Props): string => {
                return Translator.trans('admin_ui.typed', { count }, 'ibexa_admin_ui');
            };
            TSX,
            [['id' => 'admin_ui.typed', 'domain' => 'ibexa_admin_ui', 'desc' => null]],
        ];

        yield 'multiple calls in one file' => [
            'ts',
            <<<'TS'
            Translator.trans('admin_ui.first', {}, 'ibexa_admin_ui');
            Translator.trans('admin_ui.second', {}, 'ibexa_admin_ui');
            TS,
            [
                ['id' => 'admin_ui.first', 'domain' => 'ibexa_admin_ui', 'desc' => null],
                ['id' => 'admin_ui.second', 'domain' => 'ibexa_admin_ui', 'desc' => null],
            ],
        ];

        yield 'desc is ignored when comment does not directly precede the id literal' => [
            'ts',
            <<<'TS'
            /* @Desc("Unrelated") */
            const unrelated = true;

            Translator.trans('admin_ui.no_desc', {}, 'ibexa_admin_ui');
            TS,
            [['id' => 'admin_ui.no_desc', 'domain' => 'ibexa_admin_ui', 'desc' => null]],
        ];

        yield 'desc with escaped double quote inside double-quoted Desc' => [
            'ts',
            <<<'TS'
            Translator.trans(
                /* @Desc("He said \"hi\" to everyone") */ 'admin_ui.escaped_double',
                {},
                'ibexa_admin_ui'
            );
            TS,
            [['id' => 'admin_ui.escaped_double', 'domain' => 'ibexa_admin_ui', 'desc' => 'He said "hi" to everyone']],
        ];

        yield 'desc with escaped single quote inside single-quoted Desc' => [
            'ts',
            <<<'TS'
            Translator.trans(
                /* @Desc('It\'s here') */ 'admin_ui.escaped_single',
                {},
                'ibexa_admin_ui'
            );
            TS,
            [['id' => 'admin_ui.escaped_single', 'domain' => 'ibexa_admin_ui', 'desc' => "It's here"]],
        ];

        yield 'non-literal id is skipped and warns' => [
            'ts',
            <<<'TS'
            const key = 'admin_ui.dynamic';
            Translator.trans(key, {}, 'ibexa_admin_ui');
            TS,
            [],
            ['Could not extract id, expected string literal but got Identifier'],
        ];

        yield 'non-literal domain still yields the message but warns' => [
            'ts',
            <<<'TS'
            const domain = 'ibexa_admin_ui';
            Translator.trans('admin_ui.dynamic_domain', {}, domain);
            TS,
            [['id' => 'admin_ui.dynamic_domain', 'domain' => null, 'desc' => null]],
            ['Could not extract domain, expected string literal but got Identifier'],
        ];

        yield 'missing arguments entirely still warns about id' => [
            'ts',
            'Translator.trans();',
            [],
            ['Could not extract id, expected string literal but got nothing'],
        ];
    }

    public function testServeModeProcessesMultipleFilesSequentiallyAndSurvivesPerFileErrors(): void
    {
        $goodFile = tempnam(sys_get_temp_dir(), 'ts_extractor_');
        rename($goodFile, $goodFile .= '.ts');
        file_put_contents($goodFile, "Translator.trans('admin_ui.good', {}, 'ibexa_admin_ui');");

        $badFile = tempnam(sys_get_temp_dir(), 'ts_extractor_');
        rename($badFile, $badFile .= '.ts');
        file_put_contents($badFile, 'const x: = ;');

        $process = new Process(['node', $this->getScriptPath(), '--serve']);
        $process->setInput($goodFile . "\n" . $badFile . "\n" . $goodFile . "\n");
        $process->run();

        self::assertTrue($process->isSuccessful(), $process->getErrorOutput());

        $lines = array_values(array_filter(explode("\n", $process->getOutput())));
        self::assertCount(3, $lines);

        $expectedMessage = [['id' => 'admin_ui.good', 'domain' => 'ibexa_admin_ui', 'desc' => null]];

        self::assertSame($expectedMessage, json_decode($lines[0], true)['messages']);
        self::assertArrayHasKey('error', json_decode($lines[1], true));
        self::assertSame($expectedMessage, json_decode($lines[2], true)['messages']);

        unlink($goodFile);
        unlink($badFile);
    }

    public function testExitsWithNonZeroStatusOnSyntaxError(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ts_extractor_');
        rename($file, $file .= '.ts');
        file_put_contents($file, 'const x: = ;');

        $process = new Process(['node', $this->getScriptPath(), $file]);
        $process->run();

        self::assertFalse($process->isSuccessful());
        self::assertNotSame('', trim($process->getErrorOutput()));

        unlink($file);
    }

    private function getScriptPath(): string
    {
        return dirname(__DIR__, 4) . '/src/lib/Translation/Extractor/typescript_translation_extractor.mjs';
    }
}
