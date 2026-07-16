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
use SplFileInfo;
use Twig\Node\Node as TwigNode;

final class TypeScriptFileVisitor implements FileVisitorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly TypeScriptExtractorClient $extractorClient,
        private readonly string $defaultDomain = 'messages',
    ) {
        $this->logger = new NullLogger();
    }

    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): void
    {
        if (!$this->supports($file)) {
            return;
        }

        $extractedMessages = $this->extractMessages($file);

        if ($extractedMessages === null) {
            return;
        }

        foreach ($extractedMessages as $messageData) {
            if (!is_array($messageData) || !isset($messageData['id']) || !is_string($messageData['id'])) {
                continue;
            }

            $catalogue->add($this->createMessage($file, $messageData));
        }
    }

    /**
     * @return array<mixed>|null
     */
    private function extractMessages(SplFileInfo $file): ?array
    {
        $extractionResponse = $this->extractorClient->extract((string) $file->getRealPath());

        if (isset($extractionResponse['error'])) {
            $this->logger?->error(sprintf(
                'Unable to parse TypeScript file %s: %s',
                $file->getRealPath(),
                $extractionResponse['error'],
            ));

            return null;
        }

        if (!isset($extractionResponse['messages']) || !is_array($extractionResponse['messages'])) {
            $this->logger?->error(sprintf(
                'Unable to decode TypeScript extractor output for file %s.',
                $file->getRealPath(),
            ));

            return null;
        }

        $this->logWarnings($extractionResponse['warnings'] ?? []);

        return $extractionResponse['messages'];
    }

    /**
     * @param mixed $warnings
     */
    private function logWarnings(mixed $warnings): void
    {
        if (!is_array($warnings)) {
            return;
        }

        foreach ($warnings as $warning) {
            if (is_string($warning)) {
                $this->logger?->error($warning);
            }
        }
    }

    /**
     * @param array{id: string, domain?: mixed, desc?: mixed} $messageData
     */
    private function createMessage(SplFileInfo $file, array $messageData): Message
    {
        $message = new Message(
            $messageData['id'],
            isset($messageData['domain']) && is_string($messageData['domain']) ? $messageData['domain'] : $this->defaultDomain,
        );

        if (isset($messageData['desc']) && is_string($messageData['desc'])) {
            $message->setDesc($messageData['desc']);
        }

        $message->addSource(new FileSource((string) $file));

        return $message;
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
        $realPath = $file->getRealPath();

        return ($realPath !== false)
            && (str_ends_with($realPath, '.ts') || str_ends_with($realPath, '.tsx'))
            && !str_ends_with($realPath, '.d.ts');
    }
}
