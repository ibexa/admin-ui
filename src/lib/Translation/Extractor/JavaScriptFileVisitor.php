<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Translation\Extractor;

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Logger\LoggerAwareInterface;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Peast\Peast;
use Peast\Syntax\Exception;
use Peast\Syntax\Node;
use Peast\Syntax\Node\Expression;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use SplFileInfo;
use Twig\Node\Node as TwigNode;

class JavaScriptFileVisitor implements FileVisitorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const TRANSLATOR_OBJECT = 'Translator';
    public const TRANSLATOR_TRANS_METHOD = 'trans';
    public const TRANSLATOR_TRANS_CHOICE_METHOD = 'transChoice';

    public const ID_ARG = 0;
    public const TRANS_DOMAIN_ARG = 2;
    public const TRANS_CHOICE_DOMAIN_ARG = 3;

    /** @var DocParser */
    private $docParser;

    /** @var string */
    private $defaultDomain;

    /**
     * JavaScriptFileVisitor constructor.
     *
     * @param string $defaultDomain
     */
    public function __construct(string $defaultDomain = 'messages')
    {
        $this->logger = new NullLogger();
        $this->defaultDomain = $defaultDomain;

        $this->docParser = new DocParser();
        $this->docParser->setIgnoreNotImportedAnnotations(true);
        $this->docParser->setImports([
            'desc' => Desc::class,
        ]);
    }

    public function visitFile(
        SplFileInfo $file,
        MessageCatalogue $catalogue
    ) {
        if (!$this->supports($file)) {
            return;
        }

        try {
            $source = file_get_contents($file->getRealPath());

            $parser = Peast::latest($source, [
                'comments' => true,
                'jsx' => true,
                'sourceType' => Peast::SOURCE_TYPE_MODULE,
            ]);

            $ast = $parser->parse();
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                'Unable to parse file %s: %s in line %d column %d',
                $file->getRealPath(),
                $e->getMessage(),
                $e->getPosition()->getLine(),
                $e->getPosition()->getColumn()
            ));

            return;
        }

        $ast->traverse(function ($node) use ($catalogue, $file) {
            if ($this->isMethodCall($node, self::TRANSLATOR_OBJECT, self::TRANSLATOR_TRANS_METHOD)
                || $this->isMethodCall($node, self::TRANSLATOR_OBJECT, self::TRANSLATOR_TRANS_CHOICE_METHOD)
            ) {
                $arguments = $node->getArguments();
                $id = $this->extractId($file, $arguments);
                if ($id !== null) {
                    $callee = $node->getCallee();
                    $property = $callee->getProperty();

                    $message = new Message(
                        $id,
                        $this->extractDomain($file, $arguments, $property->getName()) ?? $this->defaultDomain
                    );
                    $message->setDesc($this->extractDesc($arguments));
                    $message->addSource(new FileSource((string)$file));

                    $catalogue->add($message);
                }
            }
        });
    }

    public function visitPhpFile(
        SplFileInfo $file,
        MessageCatalogue $catalogue,
        array $ast
    ) {}

    public function visitTwigFile(
        SplFileInfo $file,
        MessageCatalogue $catalogue,
        TwigNode $ast
    ) {}

    /**
     * Returns true if node is a method call.
     *
     * @param Node\Node $node
     * @param string $objectName
     * @param string $methodName
     *
     * @return bool
     */
    private function isMethodCall(
        Node\Node $node,
        string $objectName,
        string $methodName
    ): bool {
        if ($node instanceof Node\CallExpression) {
            $callee = $node->getCallee();

            if ($callee instanceof Node\MemberExpression) {
                $object = $callee->getObject();
                $property = $callee->getProperty();

                if ($object instanceof Node\Identifier && $property instanceof Node\Identifier) {
                    return $object->getName() === $objectName && $property->getName() === $methodName;
                }
            }
        }

        return false;
    }

    /**
     * Extracts a message domain from the translator call.
     *
     * @param SplFileInfo $file
     * @param Expression[] $arguments
     *
     * @return string|null
     */
    private function extractId(
        SplFileInfo $file,
        array $arguments
    ): ?string {
        if (!empty($arguments)) {
            $idNode = $arguments[self::ID_ARG];

            if (!($idNode instanceof Node\StringLiteral)) {
                $position = $idNode->getLocation()->getStart();

                $this->logger->error(sprintf(
                    'Could not extract id, expected string literal but got %s (in %s on line %d column %d).',
                    $idNode->getType(),
                    $file->getRealPath(),
                    $position->getLine(),
                    $position->getColumn()
                ));
            }

            return $idNode->getValue();
        }

        return null;
    }

    /**
     * Extracts a message domain from the translator call.
     *
     * @param SplFileInfo $file
     * @param Expression[] $arguments
     * @param string $methodName
     *
     * @return string|null
     */
    private function extractDomain(
        SplFileInfo $file,
        array $arguments,
        string $methodName
    ): ?string {
        $domainArgIndex = $methodName === self::TRANSLATOR_TRANS_METHOD
            ? self::TRANS_DOMAIN_ARG
            : self::TRANS_CHOICE_DOMAIN_ARG;

        if (isset($arguments[$domainArgIndex])) {
            $domainNode = $arguments[$domainArgIndex];

            if (!($domainNode instanceof Node\StringLiteral)) {
                $position = $domainNode->getLocation()->getStart();

                $this->logger->error(sprintf(
                    'Could not extract domain, expected string literal but got %s (in %s on line %d column %d).',
                    $domainNode->getType(),
                    $file->getRealPath(),
                    $position->getLine(),
                    $position->getColumn()
                ));
            }

            return $domainNode->getValue();
        }

        return null;
    }

    /**
     * Extracts a message description from the translator call.
     *
     * @param Expression[] $arguments
     *
     * @return string|null
     */
    private function extractDesc(array $arguments): ?string
    {
        if (!empty($arguments)) {
            foreach ($arguments[self::ID_ARG]->getLeadingComments() as $comment) {
                $annotations = $this->docParser->parse($comment->getText());
                if (!empty($annotations)) {
                    return $annotations[0]->text;
                }
            }
        }

        return null;
    }

    /**
     * Returns true if file is supported by extractor.
     *
     * @param SplFileInfo $file
     *
     * @return bool
     */
    private function supports(SplFileInfo $file): bool
    {
        return '.js' === substr($file->getRealPath(), -3) && '.min.js' !== substr($file->getRealPath(), -7);
    }
}

class_alias(JavaScriptFileVisitor::class, 'EzSystems\EzPlatformAdminUi\Translation\Extractor\JavaScriptFileVisitor');
