<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Service;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Core\Repository;

readonly class ContentTypeService
{
    /**
     * @param string[] $prioritizedLanguages
     */
    public function __construct(
        private Repository\ContentTypeService $contentTypeService,
        private array $prioritizedLanguages
    ) {
    }

    public function getContentType(int $id): ContentType
    {
        try {
            return $this->contentTypeService->loadContentTypeDraft($id);
        } catch (NotFoundException) {
            return $this->contentTypeService->loadContentType($id, $this->prioritizedLanguages);
        }
    }

    public function getContentTypeDraft(int $id): ContentTypeDraft
    {
        try {
            return $this->contentTypeService->loadContentTypeDraft($id);
        } catch (NotFoundException) {
            return $this->contentTypeService->createContentTypeDraft(
                $this->contentTypeService->loadContentType($id, $this->prioritizedLanguages)
            );
        }
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]
     */
    public function getContentTypes(ContentTypeGroup $group): array
    {
        return $this->contentTypeService->loadContentTypes(
            $group,
            $this->prioritizedLanguages
        );
    }

    public function createContentType(ContentTypeGroup $group): ContentType
    {
        $identifier = '__new__' . md5((string)microtime(true));
        $languageCode = $this->prioritizedLanguages[0];

        $createStruct = $this->contentTypeService->newContentTypeCreateStruct($identifier);
        $createStruct->mainLanguageCode = $languageCode;
        $createStruct->names = [
            $languageCode => 'New ContentType',
        ];

        return $this->contentTypeService->createContentType($createStruct, [$group]);
    }

    public function deleteContentType(ContentType $contentType): void
    {
        $this->contentTypeService->deleteContentType($contentType);
    }

    /**
     * Return the highest prioritized language that $contentType is translated to.
     * If there is no translation for a prioritized language, return $contentType's main language.
     */
    public function getPrioritizedLanguage(ContentType $contentType): string
    {
        foreach ($this->prioritizedLanguages as $prioritizedLanguage) {
            if (isset($contentType->names[$prioritizedLanguage])) {
                return $prioritizedLanguage;
            }
        }

        return $contentType->mainLanguageCode;
    }
}
