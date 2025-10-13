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

final class DashboardController extends Controller
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly PermissionResolver $permissionResolver
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function dashboardAction(): Response
    {
        $editForm = $this->formFactory->contentEdit(new ContentEditData());

        return $this->render('@ibexadesign/ui/dashboard/dashboard.html.twig', [
            'form_edit' => $editForm,
            'can_create_content' => $this->permissionResolver->hasAccess('content', 'create'),
        ]);
    }
}
