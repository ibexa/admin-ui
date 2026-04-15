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
    /** @var \JMS\TranslationBundle\Translation\FileSourceFactory */
    private $fileSourceFactory;

    /** @var \PhpParser\NodeTraverser */
    private $traverser;

    /** @var \JMS\TranslationBundle\Model\MessageCatalogue */
    private $catalogue;

    /** @var \SplFileInfo */
    private $file;

    /** @var \Doctrine\Common\Annotations\DocParser */
    private $docParser;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \PhpParser\Node */
    private $previousNode;

    /**
     * Methods and "domain" parameter offset to extract from PHP code.
     *
     * @var array method => position of the "domain" parameter
     */
    protected $methodsToExtractFrom = [
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

    public function enterNode(Node $node)
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

        $idArg = $node->args[0] ?? null;
        if (!$idArg instanceof Node\Arg) {
            return null;
        }

        $idExpr = $idArg->value;
        if (!$idExpr instanceof String_) {
            if ($ignore) {
                return null;
            }

            $message = sprintf('Can only extract the translation id from a scalar string, not from "%s". Refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($idExpr), $this->file, $idExpr->getLine());

            $this->logger->error($message);

            return null;
        }

        $id = $idExpr->value;

        $index = $this->methodsToExtractFrom[strtolower($methodCallNodeName)];
        if (isset($node->args[$index]) && $node->args[$index] instanceof Node\Arg) {
            $domainExpr = $node->args[$index]->value;
            if (!$domainExpr instanceof String_) {
                if ($ignore) {
                    return null;
                }

                $message = sprintf('Can only extract the translation domain from a scalar string, not from "%s". Refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($domainExpr), $this->file, $domainExpr->getLine());

                $this->logger->error($message);

                return null;
            }

            $domain = $domainExpr->value;
        } else {
            $domain = 'messages';
        }

        if ($this->catalogue !== null) {
            $message = new Message($id, $domain);
            $message->setDesc($desc ?? '');
            $message->setMeaning($meaning ?? '');
            if ($this->file !== null) {
                $message->addSource($this->fileSourceFactory->create($this->file, $node->getLine()));
            }

            $this->catalogue->add($message);
        }

        return null;
    }

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

    public function leaveNode(Node $node)
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
        $idArg = $node->args[0] ?? null;
        if ($idArg instanceof Node\Arg && null !== $comment = $idArg->getDocComment()) {
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

            return $comment->getText();
        }

        return null;
    }
}

class_alias(NotificationTranslationExtractor::class, 'EzSystems\EzPlatformAdminUi\Translation\Extractor\NotificationTranslationExtractor');
