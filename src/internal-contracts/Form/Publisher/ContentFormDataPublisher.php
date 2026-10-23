<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\InternalContracts\AdminUi\Form\Publisher;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

/**
 * @internal
 */
final readonly class ContentFormDataPublisher
{
    public function __construct(
        private ContentService $contentService
    ) {}

    /**
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function publish(
        ContentCreateData | ContentUpdateData $data,
        string $languageCode
    ): Content {
        $versionInfo = $this->saveDraft($data, $languageCode)->getVersionInfo();

        return $this->contentService->publishVersion(
            $versionInfo,
            [$versionInfo->getInitialLanguage()->getLanguageCode()]
        );
    }

    /**
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    private function saveDraft(
        ContentCreateData | ContentUpdateData $data,
        string $languageCode
    ): Content {
        $mainLanguageCode = $data instanceof ContentCreateData
            ? $data->mainLanguageCode
            : $data->getContentDraft()->getVersionInfo()->getContentInfo()->getMainLanguageCode();

        foreach ($data->getFieldsData() as $fieldDefIdentifier => $fieldData) {
            if ($mainLanguageCode !== $languageCode && !$fieldData->getFieldDefinition()->isTranslatable()) {
                continue;
            }

            $data->setField($fieldDefIdentifier, $fieldData->getValue(), $languageCode);
        }

        if ($data instanceof ContentCreateData) {
            return $this->contentService->createContent($data, $data->getLocationStructs());
        }

        return $this->contentService->updateContent($data->getContentDraft()->getVersionInfo(), $data);
    }
}
