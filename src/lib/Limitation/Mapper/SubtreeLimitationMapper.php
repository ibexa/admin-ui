<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Path;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

class SubtreeLimitationMapper extends UDWBasedMapper implements TranslationContainerInterface
{
    public function filterLimitationValues(Limitation $limitation)
    {
        if (!is_array($limitation->limitationValues)) {
            return;
        }

        // UDW returns an array of location IDs. If we haven't used UDW, the value is as stored: an array of path strings.
        foreach ($limitation->limitationValues as $key => $limitationValue) {
            if (preg_match('/\A\d+\z/', $limitationValue) === 1) {
                $limitation->limitationValues[$key] = $this->locationService->loadLocation($limitationValue)->pathString;
            }
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function mapLimitationValue(Limitation $limitation)
    {
        $values = [];

        foreach ($limitation->limitationValues as $pathString) {
            $pathParts = explode('/', trim($pathString, '/'));
            $locationId = (int) array_pop($pathParts);

            try {
                $this->locationService->loadLocation($locationId);
            } catch (NotFoundException $e) {
                // Skip generating limitation value as Location doesn't exist at this point
                continue;
            }

            $query = new LocationQuery([
                'filter' => new Ancestor($pathString),
                'sortClauses' => [new Path()],
            ]);

            $path = [];
            foreach ($this->searchService->findLocations($query)->searchHits as $hit) {
                $path[] = $hit->valueObject->getContentInfo();
            }

            $values[] = $path;
        }

        return $values;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('subtree'),
                'ezplatform_content_forms_policies'
            )->setDesc('Subtree'),
        ];
    }
}

class_alias(SubtreeLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\SubtreeLimitationMapper');
