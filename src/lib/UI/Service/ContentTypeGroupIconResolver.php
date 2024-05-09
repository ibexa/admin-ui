<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Service;

use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @internal
 */
final class ContentTypeGroupIconResolver extends IconResolver
{
    private const PARAM_NAME_FORMAT = 'content_type_group.%s';

    /**
     * Returns path to content type group icon.
     *
     * Path is resolved based on configuration (ibexa.system.<SCOPE>.content_type.<IDENTIFIER>). If there isn't
     * corresponding entry for given content type, then path to default icon will be returned.
     */
    public function getContentTypeGroupIcon(string $identifier): string
    {
        $slugger = new AsciiSlugger();
        $identifier = (string)$slugger->slug($identifier, '_')->lower();

        return $this->getIcon(self::PARAM_NAME_FORMAT, $identifier);
    }
}
