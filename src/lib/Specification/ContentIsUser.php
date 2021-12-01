<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

class ContentIsUser implements ContentSpecification
{
    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Checks if $contentId is an existing User content.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return bool
     */
    public function isSatisfiedBy(Content $content): bool
    {
        return $this->userService->isUser($content);
    }
}

class_alias(ContentIsUser::class, 'EzSystems\EzPlatformAdminUi\Specification\ContentIsUser');
