<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Controller;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class Controller extends AbstractController
{
    public function performAccessCheck(): void
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    public function redirectToLocation(Location $location, string $uriFragment = ''): RedirectResponse
    {
        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->contentId,
            'locationId' => $location->id,
            '_fragment' => $uriFragment,
        ]);
    }
}
