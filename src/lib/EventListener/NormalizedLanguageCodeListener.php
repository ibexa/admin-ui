<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Contracts\AdminUi\Request\ContentLanguageContext;
use Ibexa\Contracts\AdminUi\Request\Resolver\ContentLanguageCodeResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class NormalizedLanguageCodeListener implements EventSubscriberInterface
{
    private ContentLanguageCodeResolverInterface $resolver;

    public function __construct(ContentLanguageCodeResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 12],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $normalizedLanguageCode = $request->attributes->get(ContentLanguageContext::ATTRIBUTE_NAME);
        if (is_string($normalizedLanguageCode) && $normalizedLanguageCode !== '') {
            return;
        }

        if (null !== $languageCode = $this->resolver->resolve($request)) {
            $request->attributes->set(ContentLanguageContext::ATTRIBUTE_NAME, $languageCode);
        }
    }
}
