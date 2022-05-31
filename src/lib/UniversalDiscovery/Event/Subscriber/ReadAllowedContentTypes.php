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
    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface */
    private $permissionChecker;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var string[]|null */
    private $allowedContentTypesIdentifiers = null;

    public function __construct(
        PermissionResolver $permissionResolver,
        PermissionCheckerInterface $permissionChecker,
        ContentTypeService $contentTypeService
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->permissionChecker = $permissionChecker;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function getAllowedContentTypesIdentifiers(array $contentTypesAllowedViaConfig): ?array
    {
        $access = $this->permissionResolver->hasAccess('content', 'read');
        if (!\is_array($access)) {
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

class_alias(ReadAllowedContentTypes::class, 'EzSystems\EzPlatformAdminUi\UniversalDiscovery\Event\Subscriber\ReadAllowedContentTypes');
