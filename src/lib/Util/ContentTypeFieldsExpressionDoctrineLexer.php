<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Doctrine\Common\Lexer\AbstractLexer;

/**
 * @extends AbstractLexer<ContentTypeFieldsExpressionDoctrineLexer::T_*, string>
 */
final class ContentTypeFieldsExpressionDoctrineLexer extends AbstractLexer
{
    public const int T_LBRACE = 1;
    public const int T_RBRACE = 2;
    public const int T_COMMA = 3;
    public const int T_SLASH = 4;
    public const int T_WILDCARD = 5;
    public const int T_IDENTIFIER = 6;

    /**
     * @return list<string>
     */
    protected function getCatchablePatterns(): array
    {
        return [
            '[a-zA-Z_][a-zA-Z0-9_-]*',
            '\*',
            '[\{\},\/]',
        ];
    }

    /**
     * @return list<string>
     */
    protected function getNonCatchablePatterns(): array
    {
        return [
            '\s+',
        ];
    }

    protected function getType(string &$value): int
    {
        if ($value === '{') {
            return self::T_LBRACE;
        }

        if ($value === '}') {
            return self::T_RBRACE;
        }

        if ($value === ',') {
            return self::T_COMMA;
        }

        if ($value === '/') {
            return self::T_SLASH;
        }

        if ($value === '*') {
            return self::T_WILDCARD;
        }

        return self::T_IDENTIFIER;
    }
}
