<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Type\Content\Draft\ContentEditType;
use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Ibexa\Search\View\SearchView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SearchViewFilterParametersListener implements EventSubscriberInterface
{
    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var string[][] */
    private $siteAccessGroups;

    public function __construct(
        FormFactoryInterface $formFactory,
        ConfigResolverInterface $configResolver,
        RequestStack $requestStack,
        array $siteAccessGroups
    ) {
        $this->formFactory = $formFactory;
        $this->configResolver = $configResolver;
        $this->requestStack = $requestStack;
        $this->siteAccessGroups = $siteAccessGroups;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvents::FILTER_VIEW_PARAMETERS => ['onFilterViewParameters', 10],
        ];
    }

    public function onFilterViewParameters(FilterViewParametersEvent $event)
    {
        $view = $event->getView();

        if (!$view instanceof SearchView) {
            return;
        }

        if (!$this->isAdminSiteAccess($this->requestStack->getCurrentRequest())) {
            return;
        }

        $editForm = $this->formFactory->create(
            ContentEditType::class,
            new ContentEditData(),
        );

        $event->getParameterBag()->add([
            'form_edit' => $editForm->createView(),
            'user_content_type_identifier' => $this->configResolver->getParameter('user_content_type_identifier'),
        ]);
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($request->attributes->get('siteaccess'));
    }
}
