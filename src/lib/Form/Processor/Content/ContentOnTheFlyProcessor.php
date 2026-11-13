<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\Content;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Event\ContentOnTheFlyEvents;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\InternalContracts\AdminUi\Form\Publisher\ContentFormDataPublisher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final readonly class ContentOnTheFlyProcessor implements EventSubscriberInterface
{
    public function __construct(
        private ContentFormDataPublisher $contentFormDataPublisher,
        private Environment $twig
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ContentOnTheFlyEvents::CONTENT_CREATE_PUBLISH => ['processCreatePublish', 10],
            ContentOnTheFlyEvents::CONTENT_EDIT_PUBLISH => ['processEditPublish', 10],
        ];
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function processCreatePublish(FormActionEvent $event): void
    {
        $locationId = $this->publishContent($event);

        // We only need to change the response so it's compatible with UDW
        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/content_create_response.html.twig', [
                    'locationId' => $locationId,
                ])
            )
        );
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function processEditPublish(FormActionEvent $event): void
    {
        $locationId = $this->publishContent($event);

        // We only need to change the response so it's compatible with UDW
        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/content_edit_response.html.twig', [
                    'locationId' => $locationId,
                ])
            )
        );
    }

    /**
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    private function publishContent(FormActionEvent $event): ?int
    {
        /** @var ContentCreateData|ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $content = $this->contentFormDataPublisher->publish($data, $form->getConfig()->getOption('languageCode'));

        $referrerLocation = $event->getOption('referrerLocation');

        return $referrerLocation
            ? $referrerLocation->id
            : $content->getContentInfo()->getMainLocationId();
    }
}
