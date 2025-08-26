<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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

final readonly class SearchViewFilterParametersListener implements EventSubscriberInterface
{
    /**
     * @param string[][] $siteAccessGroups
     */
    public function __construct(
        private FormFactoryInterface $formFactory,
        private ConfigResolverInterface $configResolver,
        private RequestStack $requestStack,
        private array $siteAccessGroups
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvents::FILTER_VIEW_PARAMETERS => ['onFilterViewParameters', 10],
        ];
    }

    public function onFilterViewParameters(FilterViewParametersEvent $event): void
    {
        $view = $event->getView();

        if (!$view instanceof SearchView) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        if (!$this->isAdminSiteAccess($request)) {
            return;
        }

        $editForm = $this->formFactory->create(
            ContentEditType::class,
            new ContentEditData(),
        );

        $event->getParameterBag()->add([
            'form_edit' => $editForm->createView(),
            'user_content_type_identifier' => $this->configResolver->getParameter(
                'user_content_type_identifier'
            ),
        ]);
    }

    private function isAdminSiteAccess(Request $request): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy(
            $request->attributes->get('siteaccess')
        );
    }
}
