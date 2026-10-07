<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Ibexa\AdminUi\Exception\FieldTypeExpressionParserException;

final class ContentTypeFieldsExpressionParser implements ContentTypeFieldsExpressionParserInterface
{
    private ContentTypeFieldsExpressionDoctrineLexer $lexer;

    public function __construct()
    {
        $this->lexer = new ContentTypeFieldsExpressionDoctrineLexer();
    }

    public function parseExpression(string $expression): ContentTypeFieldsParsedStructure
    {
        // Content type group can be omitted, therefore we need to know how many parts are there
        $slashCount = substr_count($expression, '/');

        $this->lexer->setInput($expression);
        $this->lexer->moveNext();

        $groupTokens = null; // Content type groups are optional
        $contentTypeTokens = null;
        $fieldTokens = null;

        while ($this->lexer->lookahead !== null) {
            $this->lexer->moveNext();

            if ($slashCount === 2) {
                $groupTokens = $this->parseSection();
                $this->expectSlash();
                $contentTypeTokens = $this->parseSection();
                $this->expectSlash();
                $fieldTokens = $this->parseSection();
            } elseif ($slashCount === 1) {
                $groupTokens = null;
                $contentTypeTokens = $this->parseSection();
                $this->expectSlash();
                $fieldTokens = $this->parseSection();
            } else {
                throw new FieldTypeExpressionParserException('Invalid expression, expected one or two T_SLASH delimiters.');
            }
        }

        $structure = new ContentTypeFieldsParsedStructure(
            $groupTokens,
            $contentTypeTokens,
            $fieldTokens,
        );

        if ($structure->isAllChosen()) {
            throw new FieldTypeExpressionParserException('Choosing every possible content type field is not allowed.');
        }

        return $structure;
    }

    /**
     * @return non-empty-list<string>|null
     */
    private function parseSection(): ?array
    {
        $items = [];

        if ($this->lexer->token === null) {
            throw new FieldTypeExpressionParserException('A token inside a section cannot be empty.');
        }

        // Multiple elements between braces
        if ($this->lexer->token->isA(ContentTypeFieldsExpressionDoctrineLexer::T_LBRACE)) {
            $token = $this->getTokenFromInsideBracket();
            $items[] = $token;

            while ($this->lexer->token->isA(ContentTypeFieldsExpressionDoctrineLexer::T_COMMA)) {
                $token = $this->getTokenFromInsideBracket();
                if (!in_array($token, $items, true)) {
                    $items[] = $token;
                }
            }

            if (!$this->lexer->token->isA(ContentTypeFieldsExpressionDoctrineLexer::T_RBRACE)) {
                throw new FieldTypeExpressionParserException('Expected T_RBRACE to close the list.');
            }

            $this->lexer->moveNext();
        } else {
            // Otherwise, expect a single identifier or wildcard.
            $token = $this->expectIdentifierOrWildcard();

            if ($token === null) {
                return null;
            }

            $items[] = $token;
        }

        return $items;
    }

    private function getTokenFromInsideBracket(): string
    {
        $this->lexer->moveNext();

        $token = $this->expectIdentifierOrWildcard();
        if ($token === null) {
            throw new FieldTypeExpressionParserException('Wildcards cannot be mixed with identifiers inside the expression.');
        }

        return $token;
    }

    /**
     * @throws FieldTypeExpressionParserException
     */
    private function expectSlash(): void
    {
        if ($this->lexer->token === null) {
            throw new FieldTypeExpressionParserException(
                sprintf(
                    'Expected token of type "%s" but got "null"',
                    ContentTypeFieldsExpressionDoctrineLexer::T_SLASH,
                ),
            );
        }

        if (!$this->lexer->token->isA(ContentTypeFieldsExpressionDoctrineLexer::T_SLASH)) {
            throw new FieldTypeExpressionParserException(
                sprintf(
                    'Expected token of type "%s" but got "%s"',
                    ContentTypeFieldsExpressionDoctrineLexer::T_SLASH,
                    $this->lexer->token->type,
                ),
            );
        }

        $this->lexer->moveNext();
    }

    private function expectIdentifierOrWildcard(): ?string
    {
        if ($this->lexer->token === null) {
            throw new FieldTypeExpressionParserException(
                sprintf(
                    'Expected token of type "%s" but got "null"',
                    ContentTypeFieldsExpressionDoctrineLexer::T_SLASH,
                ),
            );
        }

        if (!in_array(
            $this->lexer->token->type,
            [
                ContentTypeFieldsExpressionDoctrineLexer::T_IDENTIFIER,
                ContentTypeFieldsExpressionDoctrineLexer::T_WILDCARD,
            ],
            true,
        )) {
            throw new FieldTypeExpressionParserException('Expected an identifier or wildcard.');
        }

        $value = $this->lexer->token->isA(ContentTypeFieldsExpressionDoctrineLexer::T_WILDCARD)
            ? null
            : $this->lexer->token->value;

        $this->lexer->moveNext();

        return $value;
    }
}
