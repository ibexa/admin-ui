<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Translation\Extractor;

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Annotation\Meaning;
use JMS\TranslationBundle\Logger\LoggerAwareInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use JMS\TranslationBundle\Translation\FileSourceFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SplFileInfo;
use Twig\Node\Node as TwigNode;

/**
 * Extracts translations from TranslatableNotificationHandler::{info,success,warning,error} method calls.
 */
class NotificationTranslationExtractor implements LoggerAwareInterface, FileVisitorInterface, NodeVisitor
{
    private FileSourceFactory $fileSourceFactory;

    private NodeTraverser $traverser;

    private ?MessageCatalogue $catalogue = null;

    private ?SplFileInfo $file = null;

    private DocParser $docParser;

    private LoggerInterface $logger;

    private ?Node $previousNode = null;

    /**
     * Methods and "domain" parameter offset to extract from PHP code.
     *
     * @var array method => position of the "domain" parameter
     */
    protected array $methodsToExtractFrom = [
        'success' => 2,
        'info' => 2,
        'warning' => 2,
        'error' => 2,
    ];

    public function __construct(DocParser $docParser, FileSourceFactory $fileSourceFactory)
    {
        $this->docParser = $docParser;
        $this->fileSourceFactory = $fileSourceFactory;
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this);
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function enterNode(Node $node): int|Node|array|null
    {
        $methodCallNodeName = null;
        if ($node instanceof Node\Expr\MethodCall) {
            $methodCallNodeName = $node->name instanceof Node\Identifier ? $node->name->name : $node->name;
        }

        if (!is_string($methodCallNodeName)
            || !in_array(
                strtolower($methodCallNodeName),
                array_map('strtolower', array_keys($this->methodsToExtractFrom)),
                true
            )) {
            $this->previousNode = $node;

            return null;
        }

        $ignore = false;
        $desc = $meaning = null;

        if (null !== ($docComment = $this->getDocCommentForNode($node))) {
            if ($docComment instanceof Doc) {
                $docComment = $docComment->getText();
            }

            foreach ($this->docParser->parse($docComment, 'file ' . $this->file . ' near line ' . $node->getLine()) as $annot) {
                if ($annot instanceof Ignore) {
                    $ignore = true;
                } elseif ($annot instanceof Desc) {
                    $desc = $annot->text;
                } elseif ($annot instanceof Meaning) {
                    $meaning = $annot->text;
                }
            }
        } else {
            return null;
        }

        if (!$node->args[0]->value instanceof String_) {
            if ($ignore) {
                return null;
            }

            $message = sprintf('Can only extract the translation id from a scalar string, not from "%s". Refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[0]->value), $this->file, $node->args[0]->value->getLine());

            $this->logger->error($message);
        }

        $id = $node->args[0]->value->value;

        $index = $this->methodsToExtractFrom[strtolower($methodCallNodeName)];
        if (isset($node->args[$index])) {
            if (!$node->args[$index]->value instanceof String_) {
                if ($ignore) {
                    return null;
                }

                $message = sprintf('Can only extract the translation domain from a scalar string, not from "%s". Refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[$index]->value), $this->file, $node->args[$index]->value->getLine());

                $this->logger->error($message);
            }

            $domain = $node->args[$index]->value->value;
        } else {
            $domain = 'messages';
        }

        if ($this->catalogue !== null) {
            $message = new Message($id, $domain);
            $message->setDesc($desc);
            $message->setMeaning($meaning);
            if ($this->file !== null) {
                $message->addSource($this->fileSourceFactory->create($this->file, $node->getLine()));
            }

            $this->catalogue->add($message);
        }

        return null;
    }

    /**
     * @param \PhpParser\Node[] $ast
     */
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast): void
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
    }

    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    public function leaveNode(Node $node): int|Node|array|null
    {
        return null;
    }

    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): void
    {
    }

    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, TwigNode $ast): void
    {
    }

    private function getDocCommentForNode(Node $node): ?string
    {
        // check if there is a doc comment for the ID argument
        // ->trans(/** @Desc("FOO") */ 'my.id')
        if (null !== $comment = $node->args[0]->getDocComment()) {
            return $comment->getText();
        }

        // this may be placed somewhere up in the hierarchy,
        // -> /** @Desc("FOO") */ trans('my.id')
        // /** @Desc("FOO") */ ->trans('my.id')
        // /** @Desc("FOO") */ $translator->trans('my.id')
        if (null !== $comment = $node->getDocComment()) {
            return $comment->getText();
        }

        if (null !== $this->previousNode && $this->previousNode->getDocComment() !== null) {
            $comment = $this->previousNode->getDocComment();

            return is_object($comment) ? $comment->getText() : $comment;
        }

        return null;
    }
}
