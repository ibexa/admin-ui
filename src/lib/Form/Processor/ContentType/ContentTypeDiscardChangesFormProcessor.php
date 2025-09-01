<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\ContentType;

use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Event\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens for and processes RepositoryForm events.
 */
final readonly class ContentTypeDiscardChangesFormProcessor implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::CONTENT_TYPE_REMOVE_DRAFT => ['processDiscardChanges', 10],
        ];
    }

    public function processDiscardChanges(FormActionEvent $event): void
    {
        /** @var \Ibexa\AdminUi\Form\Data\ContentTypeData $data */
        $data = $event->getData();
        $contentTypeDraft = $data->contentTypeDraft;

        if (empty($contentTypeDraft->getContentTypeGroups())) {
            return;
        }
        $contentTypeGroup = $contentTypeDraft->getContentTypeGroups()[0];

        $event->setResponse(
            new RedirectResponse($this->urlGenerator->generate('ibexa.content_type_group.view', [
                'contentTypeGroupId' => $contentTypeGroup->id,
            ]))
        );
    }
}
