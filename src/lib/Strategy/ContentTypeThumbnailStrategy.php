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

final readonly class ContentTypeThumbnailStrategy implements ThumbnailStrategy
{
    private const string THUMBNAIL_MIME_TYPE = 'image/svg+xml';

    public function __construct(
        private ContentTypeIconResolver $contentTypeIconResolver
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Field[] $fields
     */
    public function getThumbnail(
        ContentType $contentType,
        array $fields,
        ?VersionInfo $versionInfo = null
    ): ?Thumbnail {
        try {
            $contentTypeIcon = $this->contentTypeIconResolver->getContentTypeIcon(
                $contentType->getIdentifier()
            );

            return new Thumbnail([
                'resource' => $contentTypeIcon,
                'mimeType' => self::THUMBNAIL_MIME_TYPE,
            ]);
        } catch (ContentTypeIconNotFoundException) {
            return null;
        }
    }
}
