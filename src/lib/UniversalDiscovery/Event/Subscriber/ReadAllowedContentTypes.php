<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UniversalDiscovery\Event\Subscriber;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ReadAllowedContentTypes implements EventSubscriberInterface
{
    /** @var string[]|null */
    private ?array $allowedContentTypesIdentifiers = null;

    public function __construct(
        private readonly PermissionResolver $permissionResolver,
        private readonly PermissionCheckerInterface $permissionChecker,
        private readonly ContentTypeService $contentTypeService
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function getAllowedContentTypesIdentifiers(array $contentTypesAllowedViaConfig): ?array
    {
        $access = $this->permissionResolver->hasAccess('content', 'read');
        if (!is_array($access)) {
            return $access ? ($contentTypesAllowedViaConfig ?: null) : [null];
        }

        $restrictedContentTypesIds = $this->permissionChecker->getRestrictions($access, ContentTypeLimitation::class);
        if (empty($restrictedContentTypesIds)) {
            return $contentTypesAllowedViaConfig ?: null;
        }

        $allowedContentTypesIdentifiers = [];

        $restrictedContentTypes = $this->contentTypeService->loadContentTypeList($restrictedContentTypesIds);
        foreach ($restrictedContentTypes as $contentType) {
            $allowedContentTypesIdentifiers[] = $contentType->identifier;
        }

        $allowedContentTypesIdentifiers = count($contentTypesAllowedViaConfig)
            ? array_intersect($contentTypesAllowedViaConfig, $allowedContentTypesIdentifiers)
            : $allowedContentTypesIdentifiers;

        return empty($allowedContentTypesIdentifiers) ? [null] : array_values($allowedContentTypesIdentifiers);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve', -10],
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        $config = $event->getConfig();

        $this->allowedContentTypesIdentifiers = $this->getAllowedContentTypesIdentifiers(
            $config['allowed_content_types'] ?? []
        );

        $config['allowed_content_types'] = $this->allowedContentTypesIdentifiers;

        $event->setConfig($config);
    }
}
