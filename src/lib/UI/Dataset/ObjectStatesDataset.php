<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value as UIValue;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;

final class ObjectStatesDataset
{
    /** @var UIValue\ObjectState\ObjectState[] */
    private array $data;

    public function __construct(
        private readonly ObjectStateService $objectStateService,
        private readonly ValueFactory $valueFactory
    ) {
    }

    public function load(ContentInfo $contentInfo): self
    {
        $data = array_map(
            function (ObjectStateGroup $objectStateGroup) use ($contentInfo) {
                $hasObjectStates = !empty($this->objectStateService->loadObjectStates($objectStateGroup));
                if (!$hasObjectStates) {
                    return [];
                }

                return $this->valueFactory->createObjectState($contentInfo, $objectStateGroup);
            },
            iterator_to_array($this->objectStateService->loadObjectStateGroups())
        );

        // Get rid of empty Object State Groups
        $this->data = array_filter($data);

        return $this;
    }

    /**
     * @return UIValue\ObjectState\ObjectState[]
     */
    public function getObjectStates(): array
    {
        return $this->data;
    }
}
