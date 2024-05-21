<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\Content;

use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\ContentFormProcessor;
use Ibexa\Contracts\AdminUi\Autosave\AutosaveServiceInterface;
use Ibexa\Contracts\AdminUi\Event\AutosaveEvents;
use Ibexa\Contracts\Core\Repository\Exceptions\Exception as APIException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class AutosaveProcessor implements EventSubscriberInterface
{
    private AutosaveServiceInterface $autosaveService;

    private ContentFormProcessor $innerContentFormProcessor;

    public function __construct(
        AutosaveServiceInterface $autosaveService,
        ContentFormProcessor $innerContentFormProcessor
    ) {
        $this->autosaveService = $autosaveService;
        $this->innerContentFormProcessor = $innerContentFormProcessor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AutosaveEvents::CONTENT_AUTOSAVE => ['processAutosave', 10],
        ];
    }

    public function processAutosave(FormActionEvent $event): void
    {
        try {
            $this->autosaveService->setInProgress(true);
            $this->innerContentFormProcessor->processSaveDraft($event);
            $statusCode = Response::HTTP_OK;
        } catch (APIException $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
        } finally {
            $this->autosaveService->setInProgress(false);
        }

        $event->setResponse(
            // Response content is irrelevant as it will be overwritten by ViewRenderer anyway
            new Response(null, $statusCode)
        );
    }
}
