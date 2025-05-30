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

class SectionAssign implements EventSubscriberInterface
{
    private array $restrictedContentTypes;

    private PermissionCheckerInterface $permissionChecker;

    private ContentTypeService $contentTypeService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface $permissionChecker
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function __construct(
        PermissionResolver $permissionResolver,
        PermissionCheckerInterface $permissionChecker,
        ContentTypeService $contentTypeService
    ) {
        $this->permissionChecker = $permissionChecker;
        $this->contentTypeService = $contentTypeService;
        $hasAccess = $permissionResolver->hasAccess('section', 'assign');
        $this->restrictedContentTypes = is_array($hasAccess) ? $this->getRestrictedContentTypes($hasAccess) : [];
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve'],
        ];
    }

    /**
     * @param \Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent $event
     */
    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        $configName = $event->getConfigName();
        if ('multiple' !== $configName) {
            return;
        }

        $context = $event->getContext();
        if (
            !isset($context['type'])
            || 'section_assign' !== $context['type']
        ) {
            return;
        }

        if ($this->hasContentTypeRestrictions()) {
            $config = $event->getConfig();
            $config['allowed_content_types'] = $this->restrictedContentTypes;
            $event->setConfig($config);
        }
    }

    /**
     * @param array $hasAccess
     *
     * @return array
     */
    private function getRestrictedContentTypes(array $hasAccess): array
    {
        $restrictedContentTypesIds = $this->permissionChecker->getRestrictions($hasAccess, ContentTypeLimitation::class);
        if (empty($restrictedContentTypesIds)) {
            return [];
        }

        $restrictedContentTypesIdentifiers = [];
        $restrictedContentTypes = $this->contentTypeService->loadContentTypeList($restrictedContentTypesIds);
        foreach ($restrictedContentTypes as $restrictedContentType) {
            $restrictedContentTypesIdentifiers[] = $restrictedContentType->identifier;
        }

        return $restrictedContentTypesIdentifiers;
    }

    /**
     * @return bool
     */
    private function hasContentTypeRestrictions(): bool
    {
        return !empty($this->restrictedContentTypes);
    }
}
