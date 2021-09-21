<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\Content;

use eZ\Publish\API\Repository\Exceptions\Exception as APIException;
use EzSystems\EzPlatformContentForms\Event\FormActionEvent;
use EzSystems\EzPlatformContentForms\Form\Processor\ContentFormProcessor;
use Ibexa\Contracts\AdminUi\Event\AutosaveEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class AutosaveProcessor implements EventSubscriberInterface
{
    /** @var \EzSystems\EzPlatformContentForms\Form\Processor\ContentFormProcessor */
    private $innerContentFormProcessor;

    public function __construct(
        ContentFormProcessor $innerContentFormProcessor
    ) {
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
            $this->innerContentFormProcessor->processSaveDraft($event);
            $statusCode = Response::HTTP_OK;
        } catch (APIException $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $event->setResponse(
            // Response content is irrelevant as it will be overwritten by ViewRenderer anyway
            new Response(null, $statusCode)
        );
    }
}

class_alias(AutosaveProcessor::class, 'EzSystems\EzPlatformAdminUi\Form\Processor\Content\AutosaveProcessor');
