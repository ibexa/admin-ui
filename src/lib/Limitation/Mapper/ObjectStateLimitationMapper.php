<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ObjectStateLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface
{
    use LoggerAwareTrait;

    /**
     * @var \Ibexa\Contracts\Core\Repository\ObjectStateService
     */
    private $objectStateService;

    public function __construct(ObjectStateService $objectStateService)
    {
        $this->objectStateService = $objectStateService;
        $this->logger = new NullLogger();
    }

    protected function getSelectionChoices()
    {
        $choices = [];
        foreach ($this->objectStateService->loadObjectStateGroups() as $group) {
            foreach ($this->objectStateService->loadObjectStates($group) as $state) {
                $choices[$state->id] = $this->getObjectStateLabel($state);
            }
        }

        return $choices;
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        $values = [];

        foreach ($limitation->limitationValues as $stateId) {
            try {
                $values[] = $this->getObjectStateLabel(
                    $this->objectStateService->loadObjectState($stateId)
                );
            } catch (NotFoundException $e) {
                $this->logger->error(sprintf('Could not map the Limitation value: could not find an Object state with ID %s', $stateId));
            }
        }

        return $values;
    }

    protected function getObjectStateLabel(ObjectState $state)
    {
        $groupName = $state
            ->getObjectStateGroup()
            ->getName($state->defaultLanguageCode);

        $stateName = $state->getName($state->defaultLanguageCode);

        return $groupName . ':' . $stateName;
    }
}

class_alias(ObjectStateLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\ObjectStateLimitationMapper');
