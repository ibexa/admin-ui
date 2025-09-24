<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\View\ContentTranslateView;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ContentTranslateViewFilterParametersListener implements EventSubscriberInterface
{
    public function __construct(
        protected ContentTypeService $contentTypeService
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

        if (!$view instanceof ContentTranslateView) {
            return;
        }

        $contentType = $view->getContent()->getContentType();

        $event->getParameterBag()->add([
            'form' => $view->getFormView(),
            'location' => $view->getLocation(),
            'language' => $view->getLanguage(),
            'base_language' => $view->getBaseLanguage(),
            'content' => $view->getContent(),
            'content_type' => $contentType,
        ]);
    }
}
