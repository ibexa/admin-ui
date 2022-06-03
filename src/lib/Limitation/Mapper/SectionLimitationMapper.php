<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class SectionLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface
{
    use LoggerAwareTrait;

    /**
     * @var \Ibexa\Contracts\Core\Repository\SectionService
     */
    private $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
        $this->logger = new NullLogger();
    }

    protected function getSelectionChoices()
    {
        $choices = [];
        foreach ($this->sectionService->loadSections() as $section) {
            $choices[$section->id] = $section->name;
        }

        return $choices;
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        $values = [];
        foreach ($limitation->limitationValues as $sectionId) {
            try {
                $values[] = $this->sectionService->loadSection($sectionId);
            } catch (NotFoundException $e) {
                $this->logger->error(sprintf('Could not map the Limitation value: could not find a Section with ID %s', $sectionId));
            }
        }

        return $values;
    }
}

class_alias(SectionLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\SectionLimitationMapper');
