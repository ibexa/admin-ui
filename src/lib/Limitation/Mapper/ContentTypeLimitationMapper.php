<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ContentTypeLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    use LoggerAwareTrait;

    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
        $this->logger = new NullLogger();
    }

    /**
     * @return mixed[]
     */
    protected function getSelectionChoices(): array
    {
        $contentTypeChoices = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $group) {
            foreach ($this->contentTypeService->loadContentTypes($group) as $contentType) {
                $contentTypeChoices[$contentType->id] = $contentType->getName(
                    $contentType->mainLanguageCode
                );
            }
        }

        return $contentTypeChoices;
    }

    /**
     * @return mixed[]
     */
    public function mapLimitationValue(Limitation $limitation): array
    {
        $values = [];
        foreach ($limitation->limitationValues as $contentTypeId) {
            try {
                $values[] = $this->contentTypeService->loadContentType((int)$contentTypeId);
            } catch (NotFoundException) {
                $this->logger?->error(
                    sprintf('Could not map the Limitation value: could not find a content type with ID %s', $contentTypeId)
                );
            }
        }

        return $values;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('class'),
                'ibexa_content_forms_policies'
            )->setDesc('Content type'),
            Message::create(
                LimitationIdentifierToLabelConverter::convert('parentclass'),
                'ibexa_content_forms_policies'
            )->setDesc('Content type of Parent'),
            Message::create(
                LimitationIdentifierToLabelConverter::convert('parentgroup'),
                'ibexa_content_forms_policies'
            )->setDesc('Content type group of Parent'),
        ];
    }
}
