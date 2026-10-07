<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\Content;

use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\ContentFormProcessor;
use Ibexa\Contracts\AdminUi\Event\ContentOnTheFlyEvents;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ContentOnTheFlyProcessor implements EventSubscriberInterface
{
    /** @var Environment */
    private $twig;

    /** @var ContentFormProcessor */
    private $innerContentFormProcessor;

    public function __construct(
        Environment $twig,
        ContentFormProcessor $innerContentFormProcessor
    ) {
        $this->twig = $twig;
        $this->innerContentFormProcessor = $innerContentFormProcessor;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            ContentOnTheFlyEvents::CONTENT_CREATE_PUBLISH => ['processCreatePublish', 10],
            ContentOnTheFlyEvents::CONTENT_EDIT_PUBLISH => ['processEditPublish', 10],
        ];
    }

    /**
     * @param FormActionEvent $event
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function processCreatePublish(FormActionEvent $event)
    {
        // Rely on Content Form Processor from ContentForms to avoid unncessary code duplication
        $this->innerContentFormProcessor->processPublish($event);

        /** @var Content $content */
        $content = $event->getPayload('content');
        $referrerLocation = $event->getOption('referrerLocation');
        $locationId = $referrerLocation ? $referrerLocation->id : $content->contentInfo->mainLocationId;

        // We only need to change the response so it's compatible with UDW
        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/content_create_response.html.twig', [
                    'locationId' => $locationId,
                ])
            )
        );
    }

    public function processEditPublish(FormActionEvent $event): void
    {
        // Rely on Content Form Processor from ContentForms to avoid unncessary code duplication
        $this->innerContentFormProcessor->processPublish($event);

        /** @var Content $content */
        $content = $event->getPayload('content');
        $referrerLocation = $event->getOption('referrerLocation');
        $locationId = $referrerLocation ? $referrerLocation->id : $content->contentInfo->mainLocationId;

        // We only need to change the response so it's compatible with UDW
        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/content_edit_response.html.twig', [
                    'locationId' => $locationId,
                ])
            )
        );
    }
}

class_alias(ContentOnTheFlyProcessor::class, 'EzSystems\EzPlatformAdminUi\Form\Processor\Content\ContentOnTheFlyProcessor');
