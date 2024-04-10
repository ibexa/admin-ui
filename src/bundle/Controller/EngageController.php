<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Symfony\Component\HttpFoundation\Response;

class EngageController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function engageAction(): Response
    {
        return $this->render('@ibexadesign/ui/integration/engage.html.twig');
    }
}

class_alias(EngageController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\EngageController');
