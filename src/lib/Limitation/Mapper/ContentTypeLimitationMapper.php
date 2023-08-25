<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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

    /**
     * @var \Ibexa\Contracts\Core\Repository\ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
        $this->logger = new NullLogger();
    }

    protected function getSelectionChoices()
    {
        $contentTypeChoices = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $group) {
            foreach ($this->contentTypeService->loadContentTypes($group) as $contentType) {
                $contentTypeChoices[$contentType->id] = $contentType->getName($contentType->mainLanguageCode);
            }
        }

        return $contentTypeChoices;
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        $values = [];
        foreach ($limitation->limitationValues as $contentTypeId) {
            try {
                $values[] = $this->contentTypeService->loadContentType($contentTypeId);
            } catch (NotFoundException $e) {
                $this->logger->error(sprintf('Could not map the Limitation value: could not find a Content Type with ID %s', $contentTypeId));
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
            )->setDesc('Content Type'),
            Message::create(
                LimitationIdentifierToLabelConverter::convert('parentclass'),
                'ibexa_content_forms_policies'
            )->setDesc('Content Type of Parent'),
            Message::create(
                LimitationIdentifierToLabelConverter::convert('parentgroup'),
                'ibexa_content_forms_policies'
            )->setDesc('Content Type Group of Parent'),
        ];
    }
}

class_alias(ContentTypeLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\ContentTypeLimitationMapper');
