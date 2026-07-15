<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Translation\Extractor;

use JMS\TranslationBundle\Logger\LoggerAwareInterface;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Twig\Node\Node as TwigNode;

final class TypeScriptFileVisitor implements FileVisitorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const DEFAULT_RESPONSE_TIMEOUT_SECONDS = 30.0;

    private string $parserScriptPath;

    private ?bool $runtimeReady = null;

    private ?Process $serverProcess = null;

    private ?InputStream $serverInput = null;

    private string $serverOutputBuffer = '';

    public function __construct(
        private readonly string $defaultDomain = 'messages',
        ?string $parserScriptPath = null,
        private readonly string $nodeBinary = 'node',
        private readonly float $responseTimeoutSeconds = self::DEFAULT_RESPONSE_TIMEOUT_SECONDS,
    ) {
        $this->logger = new NullLogger();
        $this->parserScriptPath = $parserScriptPath ?? __DIR__ . '/typescript_translation_extractor.mjs';
    }

    public function __destruct()
    {
        $this->discardServerProcess();
    }

    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): void
    {
        if (!$this->supports($file)) {
            return;
        }

        $this->assertRuntimeIsReady();

        $decoded = $this->requestExtraction((string) $file->getRealPath());

        if (isset($decoded['error'])) {
            $this->logger?->error(sprintf(
                'Unable to parse TypeScript file %s: %s',
                $file->getRealPath(),
                $decoded['error'],
            ));

            return;
        }

        if (!isset($decoded['messages']) || !is_array($decoded['messages'])) {
            $this->logger?->error(sprintf(
                'Unable to decode TypeScript extractor output for file %s.',
                $file->getRealPath(),
            ));

            return;
        }

        foreach ($decoded['warnings'] ?? [] as $warning) {
            if (is_string($warning)) {
                $this->logger?->error($warning);
            }
        }

        foreach ($decoded['messages'] as $messageData) {
            if (!is_array($messageData) || !isset($messageData['id']) || !is_string($messageData['id'])) {
                continue;
            }

            $message = new Message(
                $messageData['id'],
                isset($messageData['domain']) && is_string($messageData['domain']) ? $messageData['domain'] : $this->defaultDomain,
            );

            if (isset($messageData['desc']) && is_string($messageData['desc'])) {
                $message->setDesc($messageData['desc']);
            }

            $message->addSource(new FileSource((string) $file));
            $catalogue->add($message);
        }
    }

    /**
     * @param array<mixed> $ast
     */
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast): void
    {
    }

    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, TwigNode $ast): void
    {
    }

    private function supports(SplFileInfo $file): bool
    {
        $path = $file->getRealPath();

        return ($path !== false)
            && (str_ends_with($path, '.ts') || str_ends_with($path, '.tsx'))
            && !str_ends_with($path, '.d.ts');
    }

    private function assertRuntimeIsReady(): void
    {
        if ($this->runtimeReady === true) {
            return;
        }

        if (!is_file($this->parserScriptPath)) {
            throw new RuntimeException(sprintf(
                'TypeScript translation extractor script not found: %s.',
                $this->parserScriptPath,
            ));
        }

        $process = new Process([
            $this->nodeBinary,
            $this->parserScriptPath,
            '--check-runtime',
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'TypeScript translation extractor runtime is not available. Please ensure `%s` is installed and `@typescript-eslint/typescript-estree` is available for the admin-ui package. %s',
                $this->nodeBinary,
                trim($process->getErrorOutput()),
            ));
        }

        $this->runtimeReady = true;
    }

    /**
     * @return array{messages?: mixed, warnings?: mixed, error?: string}
     */
    private function requestExtraction(string $filePath): array
    {
        $this->ensureServerStarted();

        $this->getServerInput()->write($filePath . "\n");

        return $this->readServerLine();
    }

    private function ensureServerStarted(): void
    {
        if ($this->serverProcess !== null && $this->serverProcess->isRunning()) {
            return;
        }

        $input = new InputStream();

        $process = new Process([
            $this->nodeBinary,
            $this->parserScriptPath,
            '--serve',
        ]);
        $process->setInput($input);
        $process->setTimeout(null);
        $process->start();

        $this->serverInput = $input;
        $this->serverProcess = $process;
        $this->serverOutputBuffer = '';
    }

    private function getServerInput(): InputStream
    {
        if ($this->serverInput === null) {
            throw new RuntimeException('TypeScript translation extractor input stream has not been started.');
        }

        return $this->serverInput;
    }

    private function getServerProcess(): Process
    {
        if ($this->serverProcess === null) {
            throw new RuntimeException('TypeScript translation extractor process has not been started.');
        }

        return $this->serverProcess;
    }

    /**
     * @return array{messages?: mixed, warnings?: mixed, error?: string}
     */
    private function readServerLine(): array
    {
        $process = $this->getServerProcess();
        $deadline = microtime(true) + $this->responseTimeoutSeconds;

        while (!str_contains($this->serverOutputBuffer, "\n")) {
            if (!$process->isRunning()) {
                $this->discardServerProcess();

                throw new RuntimeException(sprintf(
                    'TypeScript translation extractor process terminated unexpectedly: %s',
                    trim($process->getErrorOutput()),
                ));
            }

            if (microtime(true) > $deadline) {
                $this->discardServerProcess();

                throw new RuntimeException(
                    'Timed out waiting for the TypeScript translation extractor process to respond.',
                );
            }

            $this->serverOutputBuffer .= $process->getIncrementalOutput();

            if (!str_contains($this->serverOutputBuffer, "\n")) {
                usleep(2000);
            }
        }

        [$line, $rest] = explode("\n", $this->serverOutputBuffer, 2);
        $this->serverOutputBuffer = $rest;

        $decoded = json_decode($line, true);

        return is_array($decoded) ? $decoded : ['error' => 'Unable to decode extractor output.'];
    }

    /**
     * Kills the current server process and discards any buffered output, so that a
     * response arriving after a timeout cannot be misread as the answer to a later,
     * unrelated request once the process is restarted.
     */
    private function discardServerProcess(): void
    {
        $this->serverInput?->close();
        $this->serverProcess?->stop(3);

        $this->serverProcess = null;
        $this->serverInput = null;
        $this->serverOutputBuffer = '';
    }
}
