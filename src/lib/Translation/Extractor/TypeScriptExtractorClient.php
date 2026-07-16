<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Translation\Extractor;

use RuntimeException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

final class TypeScriptExtractorClient
{
    private const DEFAULT_RESPONSE_TIMEOUT_SECONDS = 30.0;

    private string $parserScriptPath;

    private ?bool $runtimeReady = null;

    private ?Process $serverProcess = null;

    private ?InputStream $serverInput = null;

    private string $serverOutputBuffer = '';

    public function __construct(
        ?string $parserScriptPath = null,
        private readonly string $nodeBinary = 'node',
        private readonly float $responseTimeoutSeconds = self::DEFAULT_RESPONSE_TIMEOUT_SECONDS,
    ) {
        $this->parserScriptPath = $parserScriptPath ?? __DIR__ . '/typescript_translation_extractor.mjs';
    }

    public function __destruct()
    {
        $this->discardServerProcess();
    }

    /**
     * @return array{messages?: mixed, warnings?: mixed, error?: string}
     */
    public function extract(string $filePath): array
    {
        $this->assertRuntimeIsReady();
        $this->ensureServerStarted();

        $this->getServerInput()->write($filePath . "\n");

        return $this->readServerLine();
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

    private function discardServerProcess(): void
    {
        $this->serverInput?->close();
        $this->serverProcess?->stop(3);

        $this->serverProcess = null;
        $this->serverInput = null;
        $this->serverOutputBuffer = '';
    }
}
