<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\AdminUi\Event\AddContentTypeGroupToUIConfigEvent;
use Ibexa\AdminUi\Event\FilterContentTypesEvent;
use Ibexa\AdminUi\UI\Service\ContentTypeIconResolver;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-type TContentTypeData array{
 *      id: int,
 *      identifier: string,
 *      name: string|null,
 *      isContainer: bool,
 *      thumbnail: string,
 *      href: string,
 *      isHidden: bool,
 *  }
 */
final readonly class ContentTypes implements ProviderInterface
{
    public function __construct(
        private ContentTypeService $contentTypeService,
        private UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        private ContentTypeIconResolver $contentTypeIconResolver,
        private UrlGeneratorInterface $urlGenerator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @phpstan-return array<string, array<TContentTypeData>>
     */
    public function getConfig(): array
    {
        $contentTypeGroups = [];

        $preferredLanguages = $this->userLanguagePreferenceProvider->getPreferredLanguages();
        $loadedContentTypeGroups = $this->contentTypeService->loadContentTypeGroups(
            $preferredLanguages
        );

        $eventContentTypeGroups = [];
        foreach ($loadedContentTypeGroups as $contentTypeGroup) {
            $eventContentTypeGroups[] = $contentTypeGroup;
        }

        /** @var \Ibexa\AdminUi\Event\AddContentTypeGroupToUIConfigEvent $event */
        $event = $this->eventDispatcher->dispatch(new AddContentTypeGroupToUIConfigEvent($eventContentTypeGroups));

        foreach ($event->getContentTypeGroups() as $contentTypeGroup) {
            $contentTypes = iterator_to_array($this->contentTypeService->loadContentTypes(
                $contentTypeGroup,
                $preferredLanguages
            ));

            usort($contentTypes, static function (ContentType $contentType1, ContentType $contentType2): int {
                return strnatcasecmp($contentType1->getName(), $contentType2->getName());
            });

            foreach ($contentTypes as $contentType) {
                $contentTypeGroups[$contentTypeGroup->identifier][] = $this->getContentTypeData(
                    $contentType,
                    $contentTypeGroup->isSystem,
                );
            }
        }

        /** @var \Ibexa\AdminUi\Event\FilterContentTypesEvent $event */
        $event = $this->eventDispatcher->dispatch(new FilterContentTypesEvent($contentTypeGroups));

        return $event->getContentTypeGroups();
    }

    /**
     * @phpstan-return TContentTypeData
     */
    private function getContentTypeData(ContentType $contentType, bool $isHidden): array
    {
        return [
            'id' => $contentType->id,
            'identifier' => $contentType->getIdentifier(),
            'name' => $contentType->getName(),
            'isContainer' => $contentType->isContainer(),
            'thumbnail' => $this->contentTypeIconResolver->getContentTypeIcon($contentType->getIdentifier()),
            'href' => $this->urlGenerator->generate('ibexa.rest.load_content_type', [
                'contentTypeId' => $contentType->id,
            ]),
            'isHidden' => $isHidden,
        ];
    }
}
