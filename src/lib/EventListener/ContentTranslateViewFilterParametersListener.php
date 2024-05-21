<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\View\ContentTranslateView;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * {@inheritdoc}
 */
class ContentTranslateViewFilterParametersListener implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    protected $contentTypeService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(
        ContentTypeService $contentTypeService
    ) {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ViewEvents::FILTER_VIEW_PARAMETERS => ['onFilterViewParameters', 10],
        ];
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\Event\FilterViewParametersEvent $event
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function onFilterViewParameters(FilterViewParametersEvent $event)
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
