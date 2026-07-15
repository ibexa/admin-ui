<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Translation\Extractor;

use Ibexa\AdminUi\Translation\Extractor\TypeScriptFileVisitor;
use JMS\TranslationBundle\Model\MessageCatalogue;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplFileInfo;

final class TypeScriptFileVisitorTest extends TestCase
{
    private const SERVER_FIXTURE_TEMPLATE = <<<'PHP'
        <?php

        $mode = $argv[1] ?? null;

        if ($mode === '--check-runtime') {
            exit(0);
        }

        if ($mode === '--serve') {
            file_put_contents('%s', 'x', FILE_APPEND);

            while (($line = fgets(STDIN)) !== false) {
                echo json_encode(%s) . "\n";
            }
        }
        PHP;

    private const RESTARTING_SERVER_FIXTURE_TEMPLATE = <<<'PHP'
        <?php

        $mode = $argv[1] ?? null;

        if ($mode === '--check-runtime') {
            exit(0);
        }

        if ($mode === '--serve') {
            $startCount = strlen(file_get_contents('%s'));
            file_put_contents('%s', 'x', FILE_APPEND);

            while (($line = fgets(STDIN)) !== false) {
                if ($startCount === 0) {
                    exit(1);
                }

                echo json_encode(['messages' => [], 'warnings' => []]) . "\n";
            }
        }
        PHP;

    public function testVisitFileAddsMessagesReturnedByExtractorScript(): void
    {
        [$temporaryDirectory, $filePath, $scriptPath, $startsFilePath] = $this->createFixture(<<<'JSON'
            [
                'messages' => [
                    [
                        'id' => 'fixture.translation',
                        'domain' => 'ibexa_fixture',
                        'desc' => 'Fixture translation',
                    ],
                ],
                'warnings' => [],
            ]
            JSON);

        $visitor = new TypeScriptFileVisitor(
            parserScriptPath: $scriptPath,
            nodeBinary: PHP_BINARY,
        );
        $catalogue = new MessageCatalogue();

        try {
            $visitor->visitFile(new SplFileInfo($filePath), $catalogue);

            self::assertSame('Fixture translation', $catalogue->get('fixture.translation', 'ibexa_fixture')->getDesc());
        } finally {
            $this->cleanupFixture($temporaryDirectory, $filePath, $scriptPath, $startsFilePath);
        }
    }

    public function testVisitFileReusesPersistentProcessAcrossMultipleFiles(): void
    {
        [$temporaryDirectory, $filePath, $scriptPath, $startsFilePath] = $this->createFixture(<<<'JSON'
            [
                'messages' => [
                    [
                        'id' => 'fixture.translation',
                        'domain' => 'ibexa_fixture',
                        'desc' => null,
                    ],
                ],
                'warnings' => [],
            ]
            JSON);

        $visitor = new TypeScriptFileVisitor(
            parserScriptPath: $scriptPath,
            nodeBinary: PHP_BINARY,
        );
        $catalogue = new MessageCatalogue();

        try {
            $visitor->visitFile(new SplFileInfo($filePath), $catalogue);
            $visitor->visitFile(new SplFileInfo($filePath), $catalogue);
            $visitor->visitFile(new SplFileInfo($filePath), $catalogue);

            self::assertSame('x', file_get_contents($startsFilePath));
        } finally {
            $this->cleanupFixture($temporaryDirectory, $filePath, $scriptPath, $startsFilePath);
        }
    }

    public function testVisitFileLogsWarningsReturnedByExtractorScript(): void
    {
        [$temporaryDirectory, $filePath, $scriptPath, $startsFilePath] = $this->createFixture(<<<'JSON'
            [
                'messages' => [],
                'warnings' => [
                    'Could not extract domain, expected string literal but got Identifier (in fixture.ts on line 1 column 1).',
                ],
            ]
            JSON);

        $visitor = new TypeScriptFileVisitor(
            parserScriptPath: $scriptPath,
            nodeBinary: PHP_BINARY,
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(self::stringContains('Could not extract domain'));
        $visitor->setLogger($logger);

        try {
            $visitor->visitFile(new SplFileInfo($filePath), new MessageCatalogue());
        } finally {
            $this->cleanupFixture($temporaryDirectory, $filePath, $scriptPath, $startsFilePath);
        }
    }

    public function testVisitFileFailsLoudlyWhenRuntimeIsUnavailable(): void
    {
        $temporaryDirectory = sys_get_temp_dir() . '/ibexa-ts-visitor-' . bin2hex(random_bytes(4));
        mkdir($temporaryDirectory, 0777, true);

        $filePath = $temporaryDirectory . '/fixture.ts';
        file_put_contents($filePath, 'export const fixture = true;');

        $scriptPath = $temporaryDirectory . '/fixture-parser.php';
        file_put_contents($scriptPath, <<<'PHP'
            <?php

            fwrite(STDERR, 'missing runtime');
            exit(1);
            PHP);

        $visitor = new TypeScriptFileVisitor(
            parserScriptPath: $scriptPath,
            nodeBinary: PHP_BINARY,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TypeScript translation extractor runtime is not available.');

        try {
            $visitor->visitFile(new SplFileInfo($filePath), new MessageCatalogue());
        } finally {
            unlink($scriptPath);
            unlink($filePath);
            rmdir($temporaryDirectory);
        }
    }

    public function testVisitFileRestartsServerProcessAfterUnexpectedTermination(): void
    {
        [$temporaryDirectory, $filePath, $scriptPath, $startsFilePath] = $this->createRestartingFixture();

        $visitor = new TypeScriptFileVisitor(
            parserScriptPath: $scriptPath,
            nodeBinary: PHP_BINARY,
        );

        $firstException = null;
        try {
            $visitor->visitFile(new SplFileInfo($filePath), new MessageCatalogue());
        } catch (RuntimeException $exception) {
            $firstException = $exception;
        } finally {
            self::assertSame('x', file_get_contents($startsFilePath));
        }

        self::assertNotNull($firstException);
        self::assertStringContainsString('terminated unexpectedly', $firstException->getMessage());

        try {
            $visitor->visitFile(new SplFileInfo($filePath), new MessageCatalogue());

            self::assertSame('xx', file_get_contents($startsFilePath));
        } finally {
            $this->cleanupFixture($temporaryDirectory, $filePath, $scriptPath, $startsFilePath);
        }
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function createFixture(string $responsePhpArrayLiteral): array
    {
        $temporaryDirectory = $this->createTemporaryDirectory();

        $filePath = $temporaryDirectory . '/fixture.ts';
        file_put_contents($filePath, 'export const fixture = true;');

        $startsFilePath = $temporaryDirectory . '/starts.txt';
        file_put_contents($startsFilePath, '');

        $scriptPath = $temporaryDirectory . '/fixture-parser.php';
        file_put_contents($scriptPath, sprintf(
            self::SERVER_FIXTURE_TEMPLATE,
            $startsFilePath,
            $responsePhpArrayLiteral,
        ));

        return [$temporaryDirectory, $filePath, $scriptPath, $startsFilePath];
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function createRestartingFixture(): array
    {
        $temporaryDirectory = $this->createTemporaryDirectory();

        $filePath = $temporaryDirectory . '/fixture.ts';
        file_put_contents($filePath, 'export const fixture = true;');

        $startsFilePath = $temporaryDirectory . '/starts.txt';
        file_put_contents($startsFilePath, '');

        $scriptPath = $temporaryDirectory . '/fixture-parser.php';
        file_put_contents($scriptPath, sprintf(
            self::RESTARTING_SERVER_FIXTURE_TEMPLATE,
            $startsFilePath,
            $startsFilePath,
        ));

        return [$temporaryDirectory, $filePath, $scriptPath, $startsFilePath];
    }

    private function createTemporaryDirectory(): string
    {
        $temporaryDirectory = sys_get_temp_dir() . '/ibexa-ts-visitor-' . bin2hex(random_bytes(4));

        if (!mkdir($temporaryDirectory, 0777, true) && !is_dir($temporaryDirectory)) {
            self::fail(sprintf('Unable to create temporary directory: %s', $temporaryDirectory));
        }

        return $temporaryDirectory;
    }

    private function cleanupFixture(
        string $temporaryDirectory,
        string $filePath,
        string $scriptPath,
        string $startsFilePath,
    ): void {
        unlink($scriptPath);
        unlink($filePath);
        unlink($startsFilePath);
        rmdir($temporaryDirectory);
    }
}
