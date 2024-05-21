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

class DashboardController extends Controller
{
    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    protected $formFactory;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /**
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(
        FormFactory $formFactory,
        PermissionResolver $permissionResolver
    ) {
        $this->formFactory = $formFactory;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function dashboardAction(): Response
    {
        $editForm = $this->formFactory->contentEdit(
            new ContentEditData()
        );

        return $this->render('@ibexadesign/ui/dashboard/dashboard.html.twig', [
            'form_edit' => $editForm->createView(),
            'can_create_content' => $this->permissionResolver->hasAccess('content', 'create'),
        ]);
    }
}
