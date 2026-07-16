<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Translation\Extractor;

use JsonException;
use RuntimeException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

final class TypeScriptExtractorClient
{
    private const DEFAULT_RESPONSE_TIMEOUT_SECONDS = 30.0;

    private string $parserScriptPath;

    private bool $isRuntimeReady = false;

    private ?Process $serverProcess = null;

    private ?InputStream $serverInput = null;

    private string $responseBuffer = '';

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

        return $this->readExtractionResponse();
    }

    private function assertRuntimeIsReady(): void
    {
        if ($this->isRuntimeReady) {
            return;
        }

        if (!is_file($this->parserScriptPath)) {
            throw new RuntimeException(sprintf(
                'TypeScript translation extractor script not found: %s.',
                $this->parserScriptPath,
            ));
        }

        $runtimeCheckProcess = new Process([
            $this->nodeBinary,
            $this->parserScriptPath,
            '--check-runtime',
        ]);
        $runtimeCheckProcess->run();

        if (!$runtimeCheckProcess->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'TypeScript translation extractor runtime is not available. Please ensure `%s` is installed and `@typescript-eslint/typescript-estree` is available for the admin-ui package. %s',
                $this->nodeBinary,
                trim($runtimeCheckProcess->getErrorOutput()),
            ));
        }

        $this->isRuntimeReady = true;
    }

    private function ensureServerStarted(): void
    {
        if ($this->serverProcess !== null && $this->serverProcess->isRunning()) {
            return;
        }

        $processInput = new InputStream();

        $extractorProcess = new Process([
            $this->nodeBinary,
            $this->parserScriptPath,
            '--serve',
        ]);
        $extractorProcess->setInput($processInput);
        $extractorProcess->setTimeout(null);
        $extractorProcess->start();

        $this->serverInput = $processInput;
        $this->serverProcess = $extractorProcess;
        $this->responseBuffer = '';
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
    private function readExtractionResponse(): array
    {
        $process = $this->getServerProcess();
        $responseDeadline = microtime(true) + $this->responseTimeoutSeconds;

        while (!str_contains($this->responseBuffer, "\n")) {
            if (!$process->isRunning()) {
                $this->discardServerProcess();

                throw new RuntimeException(sprintf(
                    'TypeScript translation extractor process terminated unexpectedly: %s',
                    trim($process->getErrorOutput()),
                ));
            }

            if (microtime(true) > $responseDeadline) {
                $this->discardServerProcess();

                throw new RuntimeException(
                    'Timed out waiting for the TypeScript translation extractor process to respond.',
                );
            }

            $this->responseBuffer .= $process->getIncrementalOutput();

            if (!str_contains($this->responseBuffer, "\n")) {
                usleep(2000);
            }
        }

        [$responseLine, $rest] = explode("\n", $this->responseBuffer, 2);
        $this->responseBuffer = $rest;

        return $this->decodeResponse($responseLine);
    }

    /**
     * @return array{messages?: mixed, warnings?: mixed, error?: string}
     */
    private function decodeResponse(string $responseLine): array
    {
        try {
            $responseData = json_decode($responseLine, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ['error' => 'Unable to decode extractor output.'];
        }

        if (!is_array($responseData)) {
            return ['error' => 'Extractor output must be a JSON object.'];
        }

        return $responseData;
    }

    private function discardServerProcess(): void
    {
        $this->serverInput?->close();
        $this->serverProcess?->stop(3);

        $this->serverProcess = null;
        $this->serverInput = null;
        $this->responseBuffer = '';
    }
}
