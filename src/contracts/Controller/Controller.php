<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Controller;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\User\Controller\AuthenticatedRememberedCheckTrait;
use Ibexa\Contracts\User\Controller\RestrictedControllerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class Controller extends AbstractController implements RestrictedControllerInterface
{
    use AuthenticatedRememberedCheckTrait;

    public function redirectToLocation(Location $location, string $uriFragment = ''): RedirectResponse
    {
        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->getContentId(),
            'locationId' => $location->getId(),
            '_fragment' => $uriFragment,
        ]);
    }
}
