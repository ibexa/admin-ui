<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Strategy;

use Ibexa\AdminUi\Exception\ContentTypeIconNotFoundException;
use Ibexa\AdminUi\UI\Service\ContentTypeIconResolver;
use Ibexa\Contracts\Core\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;
use Ibexa\Contracts\Core\Repository\Values\Content\Thumbnail;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

final class ContentTypeThumbnailStrategy implements ThumbnailStrategy
{
    private const THUMBNAIL_MIME_TYPE = 'image/svg+xml';

    /** @var \Ibexa\AdminUi\UI\Service\ContentTypeIconResolver */
    private $contentTypeIconResolver;

    public function __construct(
        ContentTypeIconResolver $contentTypeIconResolver
    ) {
        $this->contentTypeIconResolver = $contentTypeIconResolver;
    }

    public function getThumbnail(
        ContentType $contentType,
        array $fields,
        ?VersionInfo $versionInfo = null
    ): ?Thumbnail {
        try {
            $contentTypeIcon = $this->contentTypeIconResolver->getContentTypeIcon($contentType->getIdentifier());

            return new Thumbnail([
                'resource' => $contentTypeIcon,
                'mimeType' => self::THUMBNAIL_MIME_TYPE,
            ]);
        } catch (ContentTypeIconNotFoundException $exception) {
            return null;
        }
    }
}
