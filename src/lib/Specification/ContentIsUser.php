<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

/**
 * Checks if given content is an existing User content.
 */
final readonly class ContentIsUser implements ContentSpecification
{
    public function __construct(private UserService $userService)
    {
    }

    public function isSatisfiedBy(Content $content): bool
    {
        return $this->userService->isUser($content);
    }
}
