<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class SectionLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly SectionService $sectionService)
    {
        $this->logger = new NullLogger();
    }

    /**
     * @return mixed[]
     */
    protected function getSelectionChoices(): array
    {
        $choices = [];
        foreach ($this->sectionService->loadSections() as $section) {
            $choices[$section->getId()] = $section->getName();
        }

        return $choices;
    }

    /**
     * @return mixed[]
     */
    public function mapLimitationValue(Limitation $limitation): array
    {
        $values = [];
        foreach ($limitation->limitationValues as $sectionId) {
            try {
                $values[] = $this->sectionService->loadSection((int)$sectionId);
            } catch (NotFoundException) {
                $this->logger?->error(
                    sprintf('Could not map the Limitation value: could not find a Section with ID %s', $sectionId)
                );
            }
        }

        return $values;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('newsection'),
                'ibexa_content_forms_policies'
            )->setDesc('New Section'),
            Message::create(
                LimitationIdentifierToLabelConverter::convert('section'),
                'ibexa_content_forms_policies'
            )->setDesc('Section'),
        ];
    }
}
