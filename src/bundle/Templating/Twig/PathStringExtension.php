<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\Core\Repository\LocationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PathStringExtension extends AbstractExtension
{
    private LocationService $locationService;

    public function __construct(
        LocationService $locationService
    ) {
        $this->locationService = $locationService;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_path_to_locations',
                $this->getLocationList(...)
            ),
        ];
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getLocationList(string $pathString): array
    {
        $locationIds = array_map(
            'intval',
            explode('/', trim($pathString, '/'))
        );
        array_shift($locationIds);

        return $this->locationService->loadLocationList($locationIds);
    }
}
