<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Event\CancelEditVersionDraftEvent;
use Ibexa\AdminUi\View\ContentTranslateSuccessView;
use Ibexa\AdminUi\View\ContentTranslateView;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Event\ContentProxyTranslateEvent;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ContentEditController extends Controller
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly LocationService $locationService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function proxyTranslateAction(
        int $contentId,
        ?string $fromLanguageCode,
        string $toLanguageCode,
        ?int $locationId = null
    ): Response {
        /** @var \Ibexa\Contracts\AdminUi\Event\ContentProxyTranslateEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new ContentProxyTranslateEvent(
                $contentId,
                $fromLanguageCode,
                $toLanguageCode,
                null,
                $locationId
            )
        );

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        // Fallback to "translate"
        return $this->redirectToRoute('ibexa.content.translate', [
            'contentId' => $contentId,
            'fromLanguageCode' => $fromLanguageCode,
            'toLanguageCode' => $toLanguageCode,
        ]);
    }

    public function translateAction(ContentTranslateView $view): ContentTranslateView
    {
        return $view;
    }

    public function translationSuccessAction(ContentTranslateSuccessView $view): ContentTranslateSuccessView
    {
        return $view;
    }

    public function cancelEditVersionDraftAction(
        int $contentId,
        int $versionNo,
        int $referrerLocationId,
        string $languageCode
    ): Response {
        $content = $this->contentService->loadContent($contentId, [$languageCode], $versionNo);
        $referrerlocation = $this->locationService->loadLocation($referrerLocationId);

        $response = $this->eventDispatcher->dispatch(
            new CancelEditVersionDraftEvent(
                $content,
                $referrerlocation
            )
        )->getResponse();

        return $response ?? $this->redirectToLocation($referrerlocation);
    }
}
