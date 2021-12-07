<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData;
use Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlRemoveData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Tab\LocationView\UrlsTab;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UrlAliasController extends Controller
{
    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    protected $formFactory;

    /** @var \Ibexa\AdminUi\Form\SubmitHandler */
    protected $submitHandler;

    /** @var \Ibexa\Contracts\Core\Repository\URLAliasService */
    protected $urlAliasService;

    /**
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Ibexa\AdminUi\Form\SubmitHandler $submitHandler
     * @param \Ibexa\Contracts\Core\Repository\URLAliasService $urlAliasService
     */
    public function __construct(
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        URLAliasService $urlAliasService
    ) {
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->urlAliasService = $urlAliasService;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request): Response
    {
        $form = $this->formFactory->addCustomUrl();
        $form->handleRequest($request);

        /** @var \Ibexa\AdminUi\Form\Data\Content\CustomUrl\CustomUrlAddData $data */
        $data = $form->getData();
        $location = $data->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (CustomUrlAddData $data) {
                $this->urlAliasService->createUrlAlias(
                    $data->getLocation(),
                    $data->getPath(),
                    $data->getLanguage()->languageCode,
                    $data->isRedirect()
                );

                return $this->redirectToLocation($data->getLocation(), UrlsTab::URI_FRAGMENT);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        if ($location) {
            return $this->redirectToLocation($location, UrlsTab::URI_FRAGMENT);
        }

        return $this->redirectToRoute('ezplatform.dashboard');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request): Response
    {
        $form = $this->formFactory->removeCustomUrl();
        $form->handleRequest($request);

        $location = $form->getData()->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (CustomUrlRemoveData $data) {
                $aliasToRemoveList = [];
                foreach ($data->getUrlAliases() as $customUrlId => $selected) {
                    $aliasToRemoveList[] = $this->urlAliasService->load($customUrlId);
                }
                $this->urlAliasService->removeAliases($aliasToRemoveList);

                return $this->redirectToLocation($data->getLocation(), UrlsTab::URI_FRAGMENT);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        if ($location) {
            return $this->redirectToLocation($location, UrlsTab::URI_FRAGMENT);
        }

        return $this->redirectToRoute('ezplatform.dashboard');
    }
}

class_alias(UrlAliasController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\UrlAliasController');
