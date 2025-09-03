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
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LanguageLimitation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ContentCreate implements EventSubscriberInterface
{
    /** @var string[] */
    private array $restrictedContentTypesIdentifiers;

    /** @var string[] */
    private array $restrictedLanguagesCodes;

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function __construct(
        PermissionResolver $permissionResolver,
        private PermissionCheckerInterface $permissionChecker,
        private ContentTypeService $contentTypeService
    ) {
        $hasAccess = $permissionResolver->hasAccess('content', 'create');
        $this->restrictedContentTypesIdentifiers = $this->getRestrictedContentTypesIdentifiers($hasAccess);
        $this->restrictedLanguagesCodes = $this->getRestrictedLanguagesCodes($hasAccess);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve'],
        ];
    }

    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        if ($event->getConfigName() !== 'create') {
            return;
        }

        $config = $event->getConfig();

        if ($this->hasContentTypeRestrictions()) {
            $config['allowed_content_types'] = $this->restrictedContentTypesIdentifiers;
            $event->setConfig($config);
        }

        if ($this->hasLanguagesRestrictions()) {
            $config['content_on_the_fly']['allowed_languages'] = $this->restrictedLanguagesCodes;
            $event->setConfig($config);
        }
    }

    /**
     * @param array<mixed>|bool $hasAccess
     *
     * @return array<mixed>
     */
    private function getRestrictedContentTypesIdentifiers(array|bool $hasAccess): array
    {
        if (!\is_array($hasAccess)) {
            return [];
        }

        $restrictedContentTypesIds = $this->permissionChecker->getRestrictions(
            $hasAccess,
            ContentTypeLimitation::class
        );

        if (empty($restrictedContentTypesIds)) {
            return [];
        }

        $restrictedContentTypes = $this->contentTypeService->loadContentTypeList(
            $restrictedContentTypesIds
        );

        return array_values(array_map(static function (ContentType $contentType): string {
            return $contentType->getIdentifier();
        }, (array)$restrictedContentTypes));
    }

    private function hasContentTypeRestrictions(): bool
    {
        return !empty($this->restrictedContentTypesIdentifiers);
    }

    /**
     * @param array<mixed>|bool $hasAccess
     *
     * @return string[]
     */
    private function getRestrictedLanguagesCodes(array|bool $hasAccess): array
    {
        if (!is_array($hasAccess)) {
            return [];
        }

        $restrictedLanguagesCodes = $this->permissionChecker->getRestrictions($hasAccess, LanguageLimitation::class);

        if (empty($restrictedLanguagesCodes)) {
            return [];
        }

        return $restrictedLanguagesCodes;
    }

    private function hasLanguagesRestrictions(): bool
    {
        return !empty($this->restrictedLanguagesCodes);
    }
}
