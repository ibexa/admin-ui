<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Voter;

use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Core\Repository\Values\Content\Location;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LocationVoter implements VoterInterface
{
    private const CONTENT_VIEW_ROUTE_NAME = 'ibexa.content.view';

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function matchItem(ItemInterface $item): ?bool
    {
        foreach ($item->getExtra('routes', []) as $route) {
            $routeName = $route['route'] ?? null;
            $locationId = isset($route['parameters']['locationId']) ? (int)$route['parameters']['locationId'] : null;

            if ($routeName !== self::CONTENT_VIEW_ROUTE_NAME || $locationId === null) {
                continue;
            }

            $request = $this->requestStack->getCurrentRequest();
            $contentView = $request ? $request->attributes->get('view') : null;
            $location = $contentView instanceof ContentView ? $contentView->getLocation() : null;

            if (!$location instanceof Location) {
                continue;
            }

            if (in_array($locationId, array_map('intval', $location->getPath()), true)) {
                return true;
            }
        }

        return null;
    }
}
