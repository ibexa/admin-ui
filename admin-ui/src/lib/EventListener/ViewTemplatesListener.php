<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\ContentForms\User\View\UserCreateView;
use Ibexa\ContentForms\User\View\UserUpdateView;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Event\PreContentViewEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets the templates used by the user controller.
 */
class ViewTemplatesListener implements EventSubscriberInterface
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [MVCEvents::PRE_CONTENT_VIEW => 'setViewTemplates'];
    }

    /**
     * If the event's view has a defined template, sets the view's template identifier,
     * and the 'page_layout' parameter.
     */
    public function setViewTemplates(PreContentViewEvent $event): void
    {
        $view = $event->getContentView();
        $pageLayout = $this->configResolver->getParameter('page_layout');

        foreach ($this->getTemplatesMap() as $viewClass => $template) {
            if ($view instanceof $viewClass) {
                $view->setTemplateIdentifier($template);
                $view->addParameters(['page_layout' => $pageLayout]);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getTemplatesMap(): array
    {
        return [
            UserCreateView::class => $this->configResolver->getParameter('user_edit.templates.create'),
            UserUpdateView::class => $this->configResolver->getParameter('user_edit.templates.update'),
        ];
    }
}
