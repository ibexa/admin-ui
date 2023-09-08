<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor;

use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class ContentEditFormProcessor implements EventSubscriberInterface
{
    private RouterInterface $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ContentFormEvents::CONTENT_SAVE_DRAFT_AND_CLOSE => ['redirectToDraftsView', 5],
        ];
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     */
    public function redirectToDraftsView(FormActionEvent $event)
    {
        $form = $event->getForm();
        $formConfig = $form->getConfig();
        $defaultUrl = $this->router->generate('ibexa.content_draft.list');

        $event->setResponse(new RedirectResponse($formConfig->getAction() ?: $defaultUrl));
    }
}
