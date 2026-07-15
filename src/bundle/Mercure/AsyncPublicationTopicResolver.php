<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Mercure;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Ibexa\Contracts\Mercure\Topic\TopicResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Subscribes the content view page to the per-content async publication topic so the Versions tab
 * can refresh draft publication status badges live. Mirrors the editing-presence topic resolver.
 */
final class AsyncPublicationTopicResolver implements TopicResolverInterface
{
    private const string CONTENT_VIEW_ROUTE = 'ibexa.content.view';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
    ) {
    }

    public function resolveTopics(): array
    {
        if (!$this->isAsyncContentPublishEnabled()) {
            return [];
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return [];
        }

        if ($request->attributes->get('_route') !== self::CONTENT_VIEW_ROUTE) {
            return [];
        }

        $contentId = $request->attributes->get('contentId');

        if ($contentId === null) {
            return [];
        }

        return [sprintf('/async-publication/%s', $contentId)];
    }

    private function isAsyncContentPublishEnabled(): bool
    {
        return (bool) ($this->repositoryConfigurationProvider->getRepositoryConfig()['async_content_publish'] ?? false);
    }
}
